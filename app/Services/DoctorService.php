<?php

namespace App\Services;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DoctorService
{

    public function create(array $data)
    {

        return DB::transaction(function () use ($data) {


            // Upload Doctor Photo

            $photo = null;


            if(isset($data['photo']))
            {

                $photo = $data['photo']->store(
                    'doctors',
                    'public'
                );

            }



            // Create User Account

            $user = User::create([

                'name' => $data['name'],

                'email' => $data['email'],

                'phone' => $data['phone'],

                'password' => Hash::make(
                    $data['password']
                ),

                'role' => 'doctor',

                'status' => 1,

            ]);




            // Create Doctor Profile

            $doctor = Doctor::create([

                'user_id' => $user->id,


                'doctor_code' => 'DOC-' .
                    str_pad(
                        $user->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    ),


                'bmdc_number' =>
                    $data['bmdc_number'] ?? null,


                'speciality' =>
                    $data['speciality'] ?? null,


                'qualification' =>
                    $data['qualification'] ?? null,


                'designation' =>
                    $data['designation'] ?? null,


                'experience' =>
                    $data['experience'] ?? null,


                'gender' =>
                    $data['gender'] ?? null,


                'photo' => $photo,

                // Doctors created by the Super Admin are approved immediately
                'approval_status' => Doctor::STATUS_APPROVED,

                'approved_at' => now(),

            ]);




            // Create Doctor Wallet

            $doctor->wallet()->create([

                'balance' => 0

            ]);



            return $doctor;


        });

    }


}