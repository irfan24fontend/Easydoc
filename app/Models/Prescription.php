<?php

namespace App\Models;
use \App\Models\Concerns\HasShareLink;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Prescription extends Model
{
    protected $fillable = [
        'doctor_id', 'patient_id', 'chamber_id',
        'type', 'parent_prescription_id',
        'bp', 'pulse', 'temperature',
        'clinical_complaints', 'diagnosis', 'risk_factors',
        'investigations', 'investigation_findings', 'advice',
        'followup_date', 'followup_reminder_sent', 'charge_deducted',
        'offline_ref',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'followup_reminder_sent' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function chamber()
    {
        return $this->belongsTo(Chamber::class);
    }

    public function medicines()
    {
        return $this->hasMany(PrescriptionMedicine::class)->orderBy('sort_order');
    }

    // যে Investigation Advice থেকে এই প্রেসক্রিপশন তৈরি হয়েছে
    public function parent()
    {
        return $this->belongsTo(Prescription::class, 'parent_prescription_id');
    }

    // এই Investigation Advice এর পরে তৈরি হওয়া চূড়ান্ত প্রেসক্রিপশন
    public function children()
    {
        return $this->hasMany(Prescription::class, 'parent_prescription_id');
    }

    public function isInvestigationOnly(): bool
    {
        return $this->type === 'investigation_only';
    }

    /** Create a stable, unguessable public token used only to verify this prescription. */
    public function ensureVerificationToken(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            if ($this->verification_token) {
                return $this->verification_token;
            }

            $token = Str::random(40);

            try {
                static::query()
                    ->whereKey($this->getKey())
                    ->whereNull('verification_token')
                    ->update(['verification_token' => $token, 'updated_at' => now()]);
            } catch (UniqueConstraintViolationException) {
                continue;
            }

            $this->refresh();
        }

        throw new \RuntimeException('Unable to create a prescription verification link.');
    }
}
