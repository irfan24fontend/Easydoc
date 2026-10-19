<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorMedicine extends Model
{
    protected $fillable = [
        'doctor_id',
        'medicine_id',
        'is_favorite',
        'usage_count',
        'is_default',
        'default_dose',
        'default_dose_text',
        'default_duration',
        'default_instruction',
        'default_sort',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'is_default'  => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
