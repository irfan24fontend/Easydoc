<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = [
        'medicine_name',
        'medicine_details',
        'image_path',
        'is_active',
        'impressions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'impressions' => 'integer',
    ];
}
