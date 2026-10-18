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
        'medical_college',
        'bmdc_certificate',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'approved_at'   => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->approval_status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    /** "Dr. Name" without doubling the prefix if the name already has it. */
    public function displayName(): string
    {
        $name = trim((string) optional($this->user)->name);

        return preg_match('/^dr\.?\s/i', $name) ? $name : 'Dr. ' . $name;
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /** "12" -> "12 years", anything else is shown as typed. */
    public function experienceLabel(): ?string
    {
        if ($this->experience === null || $this->experience === '') {
            return null;
        }

        return ctype_digit((string) $this->experience)
            ? $this->experience . ((int) $this->experience === 1 ? ' year' : ' years')
            : (string) $this->experience;
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