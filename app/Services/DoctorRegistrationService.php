<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DoctorRegistrationService
{
    /**
     * Create the user + doctor record for a self-registered doctor.
     * The doctor starts as "pending" until the Super Admin approves.
     *
     * @param  array  $data  validated data (including UploadedFile: photo, bmdc_certificate)
     */
    public function register(array $data): User
    {
        $photoPath = null;
        $certificatePath = null;

        try {
            return DB::transaction(function () use ($data, &$photoPath, &$certificatePath) {
                // Photo is shown on the public profile -> public disk.
                $photoPath = $data['photo']->store('doctors', 'public');

                // Certificate is private -> local (non-public) disk, served only to the admin.
                $certificatePath = $data['bmdc_certificate']->store('doctor-documents', 'local');

                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'phone'    => $data['phone'],
                    'password' => Hash::make($data['password']),
                    'role'     => 'doctor',
                    'status'   => 'active',
                ]);

                $doctor = Doctor::create([
                    'user_id'          => $user->id,
                    'doctor_code'      => 'DOC-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                    'bmdc_number'      => $data['bmdc_number'],
                    'speciality'       => $data['speciality'],
                    'qualification'    => $data['qualification'],
                    'designation'      => $data['designation'],
                    'medical_college'  => $data['medical_college'],
                    'experience'       => (string) $data['experience'],
                    'gender'           => $data['gender'],
                    'date_of_birth'    => $data['date_of_birth'],
                    'photo'            => $photoPath,
                    'bmdc_certificate' => $certificatePath,
                    'approval_status'  => Doctor::STATUS_PENDING,
                ]);

                $doctor->wallet()->create(['balance' => 0]);

                return $user;
            });
        } catch (\Throwable $e) {
            // Do not leave orphan files behind if the database part failed.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            if ($certificatePath) {
                Storage::disk('local')->delete($certificatePath);
            }

            throw $e;
        }
    }
}
