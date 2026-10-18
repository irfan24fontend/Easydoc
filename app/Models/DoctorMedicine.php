<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorMedicine extends Model
{
    protected $fillable = ['doctor_id', 'medicine_id', 'is_favorite', 'usage_count'];

    protected $casts = ['is_favorite' => 'boolean'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}