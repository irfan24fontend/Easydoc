<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class WalletService
{
    public function credit(Wallet $wallet, $amount, $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $description) {
            $lockedWallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $lockedWallet->increment('balance', $amount);

            return WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description,
            ]);
        });
    }

    public function debit(Wallet $wallet, $amount, $description = null, ?string $idempotencyKey = null): ?WalletTransaction
    {
        try {
            return DB::transaction(function () use ($wallet, $amount, $description, $idempotencyKey) {
            $lockedWallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ($idempotencyKey !== null) {
                $existing = WalletTransaction::where('wallet_id', $lockedWallet->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $amount = round((float) $amount, 2);
            if ($amount < 0) {
                throw new \InvalidArgumentException('A wallet charge cannot be negative.');
            }

            if ((float) $lockedWallet->balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance. Please recharge and try again.');
            }

            if ($amount === 0.0) {
                return null;
            }

            $lockedWallet->decrement('balance', $amount);

            return WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => $description,
                'idempotency_key' => $idempotencyKey,
            ]);
            });
        } catch (QueryException $e) {
            // The unique key is the final guard when the database cannot lock rows (for example SQLite).
            if ($idempotencyKey !== null) {
                $existing = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();
                if ($existing) {
                    return $existing;
                }
            }

            throw $e;
        }
    }

    public function hasSufficientBalance(Wallet $wallet, $amount): bool
    {
        return (float) $wallet->balance >= (float) $amount;
    }
}
