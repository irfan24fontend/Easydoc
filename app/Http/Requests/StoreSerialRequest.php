<?php

namespace App\Http\Requests;

use App\Services\SerialService;
use Illuminate\Foundation\Http\FormRequest;

class StoreSerialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ফাঁকা থাকলে আজকের তারিখ; আজ থেকে সর্বোচ্চ ৩০ দিন পর্যন্ত advance সিরিয়াল
            'serial_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:' . SerialService::today(),
                'before_or_equal:' . SerialService::maxDate(),
            ],
            'patient_mobile' => 'required|string|max:20',
            'patient_name' => 'required|string|max:255',
            'patient_age' => 'nullable|integer|min:0|max:150',
            'patient_gender' => 'nullable|in:Male,Female,Other',
            'patient_weight' => 'nullable|numeric|min:0|max:500',
            'reason' => 'nullable|string|max:255',
            'bp' => 'nullable|string|max:20',
            'pulse' => 'nullable|string|max:20',
            'temperature' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'serial_date.after_or_equal' => 'আগের তারিখে সিরিয়াল দেওয়া যায় না।',
            'serial_date.before_or_equal' => 'সর্বোচ্চ ' . SerialService::MAX_ADVANCE_DAYS . ' দিন আগে পর্যন্ত সিরিয়াল দেওয়া যায়।',
            'serial_date.date_format' => 'তারিখ সঠিক নয়।',
        ];
    }
}