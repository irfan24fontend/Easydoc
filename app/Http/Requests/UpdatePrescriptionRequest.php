<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_name' => 'required|max:255',
            'patient_mobile' => 'required|max:20',
            'patient_age' => 'nullable|integer|min:0|max:150',
            'patient_weight' => 'nullable|numeric|min:0',
            'patient_gender' => 'nullable|in:Male,Female,Other',
            'blood_group' => 'nullable|max:10',
            'patient_address' => 'nullable|max:500',

            'bp' => 'nullable|max:20',
            'pulse' => 'nullable|max:20',
            'temperature' => 'nullable|max:20',

            'clinical_complaints' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'risk_factors' => 'nullable|string',
            'investigations' => 'nullable|string',
            'investigation_findings' => 'nullable|string',
            'advice' => 'nullable|string',
            'followup_date' => 'nullable|date',

            'medicines' => 'nullable|array',
            'medicines.*.medicine_id' => 'nullable|integer',
            'medicines.*.medicine_name' => 'required_with:medicines.*.medicine_id|nullable|string|max:255',
            'medicines.*.medicine_type' => 'nullable|string|max:50',
            'medicines.*.dose_morning' => 'nullable|numeric|min:0',
            'medicines.*.dose_noon' => 'nullable|numeric|min:0',
            'medicines.*.dose_night' => 'nullable|numeric|min:0',
            'medicines.*.dose_text' => 'nullable|string|max:100',
            'medicines.*.duration' => 'nullable|string|max:100',
            'medicines.*.instruction' => 'nullable|string|max:255',
        ];
    }
}