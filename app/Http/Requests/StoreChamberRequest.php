<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChamberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',
            'prescription_template_id' => 'nullable|exists:prescription_templates,id',
            'chamber_name' => 'required|max:255',
            'hospital_name' => 'required|max:255',
            'phone' => 'required|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required',
            'visiting_days' => 'required',
            'visiting_time' => 'required|max:255',
            'status' => 'required|boolean',
        ];
    }
}