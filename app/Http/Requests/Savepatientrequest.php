<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'mobile'      => ['required', 'string', 'max:20'],
            'age'         => ['nullable', 'integer', 'min:0', 'max:150'],
            'weight'      => ['nullable', 'numeric', 'min:0', 'max:500'],
            'gender'      => ['nullable', 'in:Male,Female,Other'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'নাম',
            'mobile' => 'মোবাইল',
            'age' => 'বয়স',
            'weight' => 'ওজন',
            'gender' => 'লিঙ্গ',
            'blood_group' => 'Blood Group',
            'address' => 'ঠিকানা',
        ];
    }
}