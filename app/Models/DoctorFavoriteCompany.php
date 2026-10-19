<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorFavoriteCompany extends Model
{
    protected $fillable = ['doctor_id', 'company', 'priority'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
