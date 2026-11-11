<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Services\InvoiceService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected InvoiceService $invoiceService,
    ) {
    }

    public function index()
    {
        $wallets = Wallet::with('doctor.user')
            ->latest()
            ->paginate(10);

        return view(
            'admin.wallets.index',
            compact('wallets')
        );
    }

    public function create(Wallet $wallet)
    {
        return view(
            'admin.wallets.create',
            compact('wallet')
        );
    }

    public function createCharge(Wallet $wallet)
    {
        $wallet->load('doctor.user');

        return view('admin.wallets.charge', [
            'wallet' => $wallet,
            'chargeToken' => (string) Str::uuid(),
        ]);
    }

    public function storeCharge(Request $request, Wallet $wallet)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:238'],
            'charge_token' => ['required', 'uuid'],
        ]);

        try {
            $transaction = $this->walletService->debit(
                $wallet,
                $data['amount'],
                'Custom charge: ' . $data['description'],
                'admin-charge:' . $wallet->id . ':' . $data['charge_token'],
            );
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['amount' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.wallets.index')
            ->with('success', '৳' . number_format((float) $transaction->amount, 2) . ' custom charge deducted from the doctor wallet.');
    }

    public function store(
        Request $request,
        Wallet $wallet
    ) {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'description'    => 'nullable|string',
            // optional: recharge form-e ei 2 ta field thakle invoice-e dekhabe
            'payment_method' => 'nullable|string|max:50',
            'reference'      => 'nullable|string|max:100',
        ]);

        $amount = (float) $request->amount;

        // Recharge ar invoice ek-i transaction-e: kono ekta fail hole duto-i bartil hobe
        $invoice = DB::transaction(function () use ($wallet, $request, $amount) {

            $balanceBefore = (float) $wallet->balance;

            $this->walletService->credit(
                $wallet,
                $amount,
                $request->description
            );

            $wallet->refresh();

            return $this->invoiceService->createForRecharge(
                $wallet,
                $amount,
                $balanceBefore,
                (float) $wallet->balance,
                $request->input('payment_method'),
                $request->input('reference'),
                $request->input('description'),   // description-i invoice-er Note hishebe jabe
            );
        });

        return redirect()
            ->route('admin.invoices.show', $invoice->id)
            ->with(
                'success',
                'Wallet recharge successful. Invoice ' . $invoice->invoice_no . ' generated.'
            );
    }
}
