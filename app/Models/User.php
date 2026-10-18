<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'role',
        'status',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function assistantProfile()
    {
        return $this->hasOne(DoctorAssistant::class);
    }

    public function templates()
    {
        return $this->hasMany(PrescriptionTemplate::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isActive(): bool
    {
        return $this->status !== 'inactive';
    }

    /** লগইনের পর যে route এ যাবে */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'super_admin' => 'admin.dashboard',
            'assistant' => 'assistant.serials.index',
            default => 'doctor.dashboard',
        };
    }
}