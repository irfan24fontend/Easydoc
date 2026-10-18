<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletInvoice extends Model
{
    protected $fillable = [
        'invoice_no', 'wallet_id', 'doctor_id', 'amount',
        'balance_before', 'balance_after',
        'payment_method', 'reference', 'note',
        'issued_by', 'issued_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'issued_at'      => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}