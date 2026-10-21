<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\RechargeRequestFormRequest;
use App\Models\Doctor;
use App\Models\RechargeRequest;
use App\Models\WalletInvoice;
use App\Models\WalletTransaction;
use App\Services\RechargeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(protected RechargeRequestService $recharges)
    {
    }

    protected function doctor(): Doctor
    {
        return auth()->user()->doctor;
    }

    /** Recharge page: how to pay + submit form + my requests. */
    public function recharge(): View
    {
        $doctor = $this->doctor();
        $wallet = $this->recharges->walletFor($doctor);

        $requests = RechargeRequest::where('doctor_id', $doctor->id)
            ->with('invoice')
            ->latest()
            ->limit(15)
            ->get();

        return view('doctor.wallet.recharge', [
            'doctor'      => $doctor,
            'wallet'      => $wallet,
            'requests'    => $requests,
            'bkash'       => config('easydoc_payment.bkash'),
            'minAmount'   => (int) config('easydoc.minimum_recharge_amount', 100),
            'pendingCount' => $requests->where('status', RechargeRequest::STATUS_PENDING)->count(),
            'maxPending'  => (int) config('easydoc_payment.max_pending_requests', 3),
        ]);
    }

    public function storeRecharge(RechargeRequestFormRequest $request): RedirectResponse
    {
        $doctor = $this->doctor();

        $pending = RechargeRequest::where('doctor_id', $doctor->id)
            ->where('status', RechargeRequest::STATUS_PENDING)
            ->count();

        if ($pending >= (int) config('easydoc_payment.max_pending_requests', 3)) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'You already have ' . $pending . ' requests waiting for approval. Please wait until they are reviewed.']);
        }

        $this->recharges->submit($doctor, $request->validated());

        return redirect()
            ->route('doctor.wallet.recharge')
            ->with('success', 'Your recharge request was sent. The balance is added after we verify the bKash payment, and you will get a notification and a paid invoice.');
    }

    /** All paid recharge invoices. */
    public function invoices(): View
    {
        $invoices = WalletInvoice::where('doctor_id', $this->doctor()->id)
            ->latest('issued_at')
            ->paginate(15);

        return view('doctor.wallet.invoices', compact('invoices'));
    }

    /** One invoice (print / save as PDF). */
    public function invoice(WalletInvoice $invoice): View
    {
        $doctor = $this->doctor();

        abort_unless($invoice->doctor_id === $doctor->id, 404);

        $invoice->load('issuer');

        return view('doctor.wallet.invoice', compact('invoice', 'doctor'));
    }

    /** Where the money went: every credit and debit with the balance after it. */
    public function log(Request $request): View
    {
        $doctor = $this->doctor();
        $wallet = $this->recharges->walletFor($doctor);

        $type = in_array($request->query('type'), ['credit', 'debit'], true) ? $request->query('type') : null;

        $base = WalletTransaction::where('wallet_id', $wallet->id);

        $transactions = (clone $base)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Balance after each row. Only meaningful (and only shown) when the list is not filtered.
        if (! $type && $transactions->isNotEmpty()) {
            $newerDelta = (float) (clone $base)
                ->where('id', '>', $transactions->first()->id)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as d")
                ->value('d');

            $running = (float) $wallet->balance - $newerDelta;

            foreach ($transactions as $tx) {
                $tx->balance_after = $running;
                $running -= $tx->type === 'credit' ? (float) $tx->amount : -(float) $tx->amount;
            }
        }

        $monthStart = now()->startOfMonth();

        $summary = [
            'balance'        => (float) $wallet->balance,
            'total_in'       => (float) (clone $base)->where('type', 'credit')->sum('amount'),
            'total_out'      => (float) (clone $base)->where('type', 'debit')->sum('amount'),
            'this_month_out' => (float) (clone $base)->where('type', 'debit')->where('created_at', '>=', $monthStart)->sum('amount'),
        ];

        return view('doctor.wallet.log', compact('transactions', 'summary', 'type'));
    }
}
