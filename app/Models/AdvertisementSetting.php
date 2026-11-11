<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertisementSetting extends Model
{
    public const SINGLETON_ID = 1;

    protected $fillable = [
        'id',
        'interval_minutes',
        'updated_by',
    ];

    protected $casts = [
        'interval_minutes' => 'integer',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => self::SINGLETON_ID],
            ['interval_minutes' => 10]
        );
    }
}
