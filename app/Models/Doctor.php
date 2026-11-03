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
        'investigation_advice_charge',
        'ai_diagnosis_charge',
        'ai_medicine_charge',
        'ai_interaction_charge',
        'chamber_request_charge',
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
        return $this->getCharge('prescription');
    }

    public function getCharge(string $type): float
    {
        if ($type === 'reprint') {
            return 0.0;
        }

        $map = [
            'prescription' => ['prescription_charge', 'default_prescription_charge'],
            'investigation_advice' => ['investigation_advice_charge', 'investigation_advice_charge'],
            'ai_diagnosis' => ['ai_diagnosis_charge', 'ai_diagnosis_charge'],
            'ai_medicine' => ['ai_medicine_charge', 'ai_medicine_charge'],
            'ai_interaction' => ['ai_interaction_charge', 'ai_interaction_charge'],
            'chamber_request' => ['chamber_request_charge', 'chamber_request_charge'],
        ];

        if (! isset($map[$type])) {
            throw new \InvalidArgumentException("Unknown charge type: {$type}");
        }

        [$column, $configKey] = $map[$type];

        return (float) ($this->{$column} ?? config("easydoc.{$configKey}", 0));
    }
}
