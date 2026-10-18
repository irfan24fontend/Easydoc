<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Wallet extends Model
{

    protected $fillable = [

        'doctor_id',
        'balance'

    ];


    public function doctor()
    {

        return $this->belongsTo(Doctor::class);

    }


    public function transactions()
    {

        return $this->hasMany(
            WalletTransaction::class
        );

    }

}