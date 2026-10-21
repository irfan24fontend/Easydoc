<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNotification extends Model
{
    protected $fillable = [
        'doctor_id',
        'announcement_id',
        'type',
        'title',
        'body',
        'link_url',
        'read_at',
        'popup_at',
    ];

    protected $casts = [
        'read_at'  => 'datetime',
        'popup_at' => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'feature'  => 'New feature',
            'news'     => 'News',
            'recharge' => 'Wallet',
            default    => 'Notice',
        };
    }
}
