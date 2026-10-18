<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletInvoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createForRecharge(
        Wallet $wallet,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        ?string $paymentMethod = null,
        ?string $reference = null,
        ?string $note = null,
    ): WalletInvoice {
        return DB::transaction(function () use ($wallet, $amount, $balanceBefore, $balanceAfter, $paymentMethod, $reference, $note) {
            $invoice = WalletInvoice::create([
                'wallet_id'      => $wallet->id,
                'doctor_id'      => $wallet->doctor_id,
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'payment_method' => $paymentMethod,
                'reference'      => $reference,
                'note'           => $note,
                'issued_by'      => auth()->id(),
                'issued_at'      => now(),
            ]);

            $invoice->update([
                'invoice_no' => 'INV-' . now()->format('Ym') . '-' . str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT),
            ]);

            return $invoice;
        });
    }
}