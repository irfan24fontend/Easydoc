<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\RechargeRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletInvoice;
use Illuminate\Support\Facades\DB;

class RechargeRequestService
{
    public function __construct(
        protected WalletService $wallets,
        protected InvoiceService $invoices,
        protected NotificationService $notifications,
    ) {
    }

    /** The doctor's wallet (created if it somehow does not exist yet). */
    public function walletFor(Doctor $doctor): Wallet
    {
        return Wallet::firstOrCreate(['doctor_id' => $doctor->id], ['balance' => 0]);
    }

    /** Doctor submits a bKash payment for review. */
    public function submit(Doctor $doctor, array $data): RechargeRequest
    {
        $wallet = $this->walletFor($doctor);

        return RechargeRequest::create([
            'doctor_id'      => $doctor->id,
            'wallet_id'      => $wallet->id,
            'amount'         => $data['amount'],
            'payment_method' => 'bkash',
            'sender_number'  => $data['sender_number'],
            'trx_id'         => $data['trx_id'],
            'doctor_note'    => $data['doctor_note'] ?? null,
            'status'         => RechargeRequest::STATUS_PENDING,
        ]);
    }

    /**
     * Super Admin approves: balance is added, a PAID invoice is issued and the doctor is notified.
     * All steps happen in one database transaction.
     *
     * @throws \RuntimeException when the request was already reviewed or its TrxID was already used
     */
    public function approve(RechargeRequest $request, User $admin): WalletInvoice
    {
        $invoice = DB::transaction(function () use ($request, $admin) {
            $req = RechargeRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw new \RuntimeException('This request has already been reviewed.');
            }

            $alreadyUsed = RechargeRequest::where('trx_id', $req->trx_id)
                ->where('status', RechargeRequest::STATUS_APPROVED)
                ->where('id', '!=', $req->id)
                ->exists();

            if ($alreadyUsed) {
                throw new \RuntimeException('This TrxID was already approved in another request.');
            }

            $wallet = Wallet::whereKey($req->wallet_id)->lockForUpdate()->firstOrFail();
            $before = (float) $wallet->balance;
            $amount = (float) $req->amount;

            $this->wallets->credit($wallet, $amount, 'bKash recharge (TrxID ' . $req->trx_id . ')');
            $wallet->refresh();

            $invoice = $this->invoices->createForRecharge(
                $wallet,
                $amount,
                $before,
                (float) $wallet->balance,
                'bKash',
                $req->trx_id,
                'Paid from bKash ' . $req->sender_number,
            );

            $req->update([
                'status'            => RechargeRequest::STATUS_APPROVED,
                'reviewed_by'       => $admin->id,
                'reviewed_at'       => now(),
                'wallet_invoice_id' => $invoice->id,
                'admin_note'        => null,
            ]);

            return $invoice;
        });

        $this->notifications->notifyDoctor(
            $request->doctor,
            'recharge',
            'Recharge approved',
            '৳' . number_format((float) $request->amount, 2) . ' has been added to your wallet. Your paid invoice ' . $invoice->invoice_no . ' is ready to download.',
            route('doctor.wallet.invoices.show', $invoice->id, false),
        );

        return $invoice;
    }

    /** Super Admin rejects with a reason the doctor can read. */
    public function reject(RechargeRequest $request, User $admin, string $reason): void
    {
        DB::transaction(function () use ($request, $admin, $reason) {
            $req = RechargeRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw new \RuntimeException('This request has already been reviewed.');
            }

            $req->update([
                'status'      => RechargeRequest::STATUS_REJECTED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_note'  => $reason,
            ]);
        });

        $this->notifications->notifyDoctor(
            $request->doctor,
            'recharge',
            'Recharge request rejected',
            'Your request for ৳' . number_format((float) $request->amount, 2) . ' (TrxID ' . $request->trx_id . ') was not approved. Reason: ' . $reason,
            route('doctor.wallet.recharge', [], false),
        );
    }
}
