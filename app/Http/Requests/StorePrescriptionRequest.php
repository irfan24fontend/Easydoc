<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isInvestigation = $this->input('type') === 'investigation_only';

        return static::rulesFor($isInvestigation);
    }

    /**
     * একই rule-set এখান থেকে এবং PrescriptionController::syncOffline()
     * (অফলাইনে তৈরি একাধিক prescription একসাথে sync করার endpoint) — দুই জায়গা থেকেই
     * ব্যবহার হয়, যাতে ভবিষ্যতে rule বদলালে দুই জায়গায় আলাদা করে মনে করে বদলাতে না হয়।
     */
    public static function rulesFor(bool $isInvestigation): array
    {
        return [
            'type' => 'nullable|in:full,investigation_only',

            'parent_prescription_id' =>
                'nullable|integer|exists:prescriptions,id',

            'serial_id' => 'nullable|integer|exists:serials,id',

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

            'investigations' => $isInvestigation
                ? 'required|string'
                : 'nullable|string',

            'investigation_findings' => 'nullable|string',
            'advice' => 'nullable|string',

            'followup_date' => 'nullable|date|after_or_equal:today',

            'medicines' => $isInvestigation
                ? 'nullable|array'
                : 'required|array|min:1',

            'medicines.*.medicine_id' => 'nullable|integer',

            'medicines.*.medicine_name' => $isInvestigation
                ? 'nullable|string|max:255'
                : 'required|string|max:255',

            'medicines.*.medicine_type' => 'nullable|string|max:50',
            'medicines.*.dose_morning' => 'nullable|numeric|min:0',
            'medicines.*.dose_noon' => 'nullable|numeric|min:0',
            'medicines.*.dose_night' => 'nullable|numeric|min:0',
            'medicines.*.dose_text' => 'nullable|string|max:100',
            'medicines.*.duration' => 'nullable|string|max:100',
            'medicines.*.instruction' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'investigations.required' =>
                'Investigation Advice দিতে হলে অন্তত একটা Investigation লিখতে হবে।',

            'medicines.required' => 'অন্তত একটা মেডিসিন যোগ করুন।',
        ];
    }
}

