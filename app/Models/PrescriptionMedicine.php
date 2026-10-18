<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionMedicine extends Model
{
    protected $fillable = [
        'prescription_id', 'medicine_id', 'medicine_name', 'medicine_type',
        'dose_morning', 'dose_noon', 'dose_night', 'dose_text',
        'duration', 'instruction', 'sort_order',
    ];

    protected $prefixMap = [
        'Tablet' => 'Tab.',
        'Capsule' => 'Cap.',
        'Syrup' => 'Syr.',
        'Injection' => 'Inj.',
        'Drop' => 'Drop',
        'Cream' => 'Cream',
        'Inhaler' => 'Inhaler',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function displayName(): string
    {
        $prefix = $this->prefixMap[$this->medicine_type] ?? '';
        return trim($prefix . ' ' . $this->medicine_name);
    }

    public function doseLabel(): string
    {
        if (!empty($this->dose_text)) {
            return $this->dose_text;
        }

        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 1), '0'), '.') ?: '0';
        return $fmt($this->dose_morning) . '-' . $fmt($this->dose_noon) . '-' . $fmt($this->dose_night);
    }
}