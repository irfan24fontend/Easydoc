<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class DoctorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->filled('email')) {
            $merge['email'] = strtolower(trim((string) $this->input('email')));
        }

        if ($this->filled('bmdc_number')) {
            $merge['bmdc_number'] = strtoupper(preg_replace('/\s+/', '', (string) $this->input('bmdc_number')));
        }

        if ($this->filled('phone')) {
            // "+880 1712-345678" / "8801712345678" -> "01712345678"
            $phone = preg_replace('/[\s\-()]/', '', (string) $this->input('phone'));
            $phone = preg_replace('/^(\+?88)/', '', $phone);
            $merge['phone'] = $phone;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            // Account
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:' . User::class . ',email'],
            'phone'    => ['required', 'regex:/^01[3-9][0-9]{8}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Professional
            'bmdc_number'     => ['required', 'regex:/^[A-Z0-9][A-Z0-9\-\/]{2,19}$/', 'unique:doctors,bmdc_number'],
            'speciality'      => ['required', 'string', 'max:120'],
            'qualification'   => ['required', 'string', 'max:255'],
            'designation'     => ['required', 'string', 'max:150'],
            'medical_college' => ['required', 'string', 'max:190'],
            'experience'      => ['required', 'integer', 'min:0', 'max:60'],

            // Personal
            'gender'        => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(22)->toDateString()],

            // Documents
            'photo'            => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'bmdc_certificate' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],

            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex'                   => 'Enter a valid Bangladeshi mobile number, e.g. 01712345678.',
            'phone.unique'                  => 'This mobile number is already registered.',
            'bmdc_number.regex'             => 'Enter your BMDC registration number as printed on your certificate (letters, numbers, - or /).',
            'bmdc_number.unique'            => 'This BMDC number is already registered. If it is yours, contact support.',
            'date_of_birth.before_or_equal' => 'Please check your date of birth.',
            'terms.accepted'                => 'Please confirm that the information you provided is correct.',
            'photo.max'                     => 'The photo must be 2 MB or smaller.',
            'bmdc_certificate.max'          => 'The certificate file must be 4 MB or smaller.',
        ];
    }
}
