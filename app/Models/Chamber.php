<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Chamber extends Model
{
    protected $fillable = [
        'doctor_id',
        'prescription_template_id',
        'chamber_name',
        'hospital_name',
        'phone',
        'email',
        'address',
        'visiting_days',
        'visiting_time',
        'status',          // Super Admin-er onumoti
        'doctor_active',   // Doctor-er nijer switch
    ];

    protected $casts = [
        'status'        => 'boolean',
        'doctor_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function template()
    {
        return $this->belongsTo(PrescriptionTemplate::class, 'prescription_template_id');
    }

    /** Chamber ekhon prescription-er jonno chalu (Super Admin Active + Doctor chalu) */
    public function isOperational(): bool
    {
        return $this->status && $this->doctor_active;
    }

    public function scopeOperational(Builder $query): Builder
    {
        return $query->where('status', true)->where('doctor_active', true);
    }
}