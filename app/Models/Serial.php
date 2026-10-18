<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
    protected $fillable = [
        'doctor_id', 'chamber_id', 'patient_id', 'created_by', 'prescription_id',
        'serial_date', 'serial_number', 'status',
        'reason', 'bp', 'pulse', 'temperature', 'called_at',
    ];

    protected $casts = [
        'serial_date' => 'date',
        'called_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function chamber()
    {
        return $this->belongsTo(Chamber::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['waiting', 'in_progress']);
    }

    public function statusLabel(): string
    {
        return [
            'waiting' => 'অপেক্ষায়',
            'in_progress' => 'চলছে',
            'done' => 'সম্পন্ন',
            'skipped' => 'Skip',
            'cancelled' => 'বাতিল',
        ][$this->status] ?? $this->status;
    }

    public function badgeClass(): string
    {
        return [
            'waiting' => 'bg-warning-subtle text-warning-emphasis',
            'in_progress' => 'bg-primary-subtle text-primary-emphasis',
            'done' => 'bg-success-subtle text-success-emphasis',
            'skipped' => 'bg-secondary-subtle text-secondary-emphasis',
            'cancelled' => 'bg-danger-subtle text-danger-emphasis',
        ][$this->status] ?? 'bg-light text-dark';
    }
}