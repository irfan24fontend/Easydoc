<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChamberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'prescription_template_id' => ['nullable', 'exists:prescription_templates,id'],
            'chamber_name' => ['required', 'string', 'max:255'],
            'hospital_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'visiting_days' => ['required', 'string', 'max:255'],
            'visiting_time' => ['required', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
        ];
    }
}
