<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorAssistant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AssistantService
{
    public function create(Doctor $doctor, array $data): DoctorAssistant
    {
        return DB::transaction(function () use ($doctor, $data) {

            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'assistant',
                'status' => 'active',
            ]);

            return DoctorAssistant::create([
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
            ]);
        });
    }

    public function update(DoctorAssistant $assistant, array $data): void
    {
        $payload = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'status' => $data['status'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $assistant->user->update($payload);
    }

    public function delete(DoctorAssistant $assistant): void
    {
        // user মুছলে doctor_assistants এর row foreign key cascade এ নিজে মুছে যাবে
        $assistant->user->delete();
    }
}