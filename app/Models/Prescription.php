<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = [
        'doctor_id', 'patient_id', 'chamber_id',
        'type', 'parent_prescription_id',
        'bp', 'pulse', 'temperature',
        'clinical_complaints', 'diagnosis', 'risk_factors',
        'investigations', 'investigation_findings', 'advice',
        'followup_date', 'followup_reminder_sent', 'charge_deducted',
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
}