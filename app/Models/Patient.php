<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'doctor_id', 'patient_no', 'name', 'mobile', 'age', 'weight', 'gender', 'blood_group', 'address',
    ];

    /**
     * Patient ID, jemon: PT-0007
     * (proti doctor-er nijer patient-der jonno alada serial)
     */
    public function getPatientCodeAttribute(): ?string
    {
        if (empty($this->patient_no)) {
            return null;
        }

        return 'PT-' . str_pad((string) $this->patient_no, 4, '0', STR_PAD_LEFT);
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
     * Naam, mobile ba Patient ID (PT-0007 / pt7 / 7) diye khuja
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

            if (preg_match('/^PT-?\s*(\d+)$/i', $term, $m)) {
                $w->orWhere('patient_no', (int) $m[1]);
            } elseif (ctype_digit($term) && strlen($term) <= 5) {
                $w->orWhere('patient_no', (int) $term);
            }
        });
    }
}