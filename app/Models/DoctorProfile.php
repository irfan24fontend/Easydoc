<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    protected $fillable = [
        'doctor_id',
        'slug',
        'tagline',
        'about',
        'education',
        'work_experience',
        'expertise',
        'languages',
        'consultation_fee',
        'website_url',
        'facebook_url',
        'show_contact',
        'is_published',
    ];

    protected $casts = [
        'education'        => 'array',
        'work_experience'  => 'array',
        'expertise'        => 'array',
        'languages'        => 'array',
        'consultation_fee' => 'integer',
        'show_contact'     => 'boolean',
        'is_published'     => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function url(): string
    {
        return route('public.doctor.show', $this->slug);
    }

    /** Visible to everyone: published by the doctor AND the doctor is approved + active. */
    public function isLive(): bool
    {
        $doctor = $this->doctor;

        return $this->is_published
            && $doctor
            && $doctor->isApproved()
            && $doctor->user
            && $doctor->user->isActive();
    }
}
