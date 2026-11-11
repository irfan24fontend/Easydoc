<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    public const PATIENT_CODE_START = 12547;

    protected $fillable = [
        'doctor_id', 'patient_no', 'name', 'mobile', 'age', 'weight', 'gender', 'blood_group', 'address',
    ];

    /**
     * Display a doctor's patient sequence starting at 12547.
     */
    public function getPatientCodeAttribute(): ?string
    {
        if (empty($this->patient_no)) {
            return null;
        }

        return (string) (self::PATIENT_CODE_START + (int) $this->patient_no - 1);
    }

    /** Resolve the new numeric code and older PT/numeric IDs to the stored sequence. */
    public static function patientNoFromSearchTerm(?string $term): ?int
    {
        $term = trim((string) $term);

        if (preg_match('/^PT-?\s*(\d+)$/i', $term, $matches)) {
            return (int) $matches[1];
        }

        if (! preg_match('/^\d{1,9}$/', $term)) {
            return null;
        }

        $number = (int) $term;

        if ($number >= self::PATIENT_CODE_START) {
            return $number - self::PATIENT_CODE_START + 1;
        }

        // Keep older short numeric searches working for existing records.
        return strlen($term) <= 4 ? $number : null;
    }

    /** Avatar-er jonno naam-er prothom 2 okkhor */
    public function getInitialsAttribute(): string
    {
        $name = trim((string) $this->name);

        return $name === '' ? 'PT' : mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class)->latest();
    }

    public function serials()
    {
        return $this->hasMany(Serial::class);
    }

    /**
     * Search by name, mobile, the new numeric code, or an older PT code.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function ($w) use ($term) {
            $w->where('name', 'like', "%{$term}%")
              ->orWhere('mobile', 'like', "%{$term}%");

            $patientNo = self::patientNoFromSearchTerm($term);
            if ($patientNo !== null) {
                $w->orWhere('patient_no', $patientNo);
            }
        });
    }
}
