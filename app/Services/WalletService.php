<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;


class WalletService
{


    public function credit(
        Wallet $wallet,
        $amount,
        $description = null
    )
    {

        return DB::transaction(function () use (
            $wallet,
            $amount,
            $description
        ) {


            $wallet->increment(
                'balance',
                $amount
            );


            return WalletTransaction::create([

                'wallet_id'=>$wallet->id,

                'type'=>'credit',

                'amount'=>$amount,

                'description'=>$description

            ]);


        });
        


    }

public function debit(Wallet $wallet, $amount, $description = null)
{
    return DB::transaction(function () use ($wallet, $amount, $description) {

        $wallet->refresh();

        if ($wallet->balance < $amount) {
            throw new \Exception('অপর্যাপ্ত ব্যালেন্স। অনুগ্রহ করে সুপার এডমিনের সাথে যোগাযোগ করে রিচার্জ করুন।');
        }

        $wallet->decrement('balance', $amount);

        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description,
        ]);
    });
}

public function hasSufficientBalance(Wallet $wallet, $amount): bool
{
    return $wallet->balance >= $amount;
}
}