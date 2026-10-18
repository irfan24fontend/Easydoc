<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_code',
        'bmdc_number',
        'speciality',
        'qualification',
        'designation',
        'experience',
        'gender',
        'date_of_birth',
        'photo',
        'about',
        'prescription_charge',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chambers()
    {
        return $this->hasMany(Chamber::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function assistants()
    {
        return $this->hasMany(DoctorAssistant::class);
    }

    public function serials()
    {
        return $this->hasMany(Serial::class);
    }

    public function activeChamber()
{
    return $this->chambers()
        ->where('status', true)        // Super Admin Active
        ->where('doctor_active', true) // Doctor nije chalu rekheche
        ->first();
}

    public function getPrescriptionCharge(): float
    {
        return (float) ($this->prescription_charge ?? config('easydoc.default_prescription_charge'));
    }
}