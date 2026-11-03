<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssistantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'chamber_ids' => 'required|array|min:1',
            'chamber_ids.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('chambers', 'id')->where('doctor_id', auth()->user()->doctor?->id),
            ],
        ];
    }
}
