<?php

namespace App\Http\Requests;

use App\Models\DoctorAssistant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssistantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('assistant');
        $userId = $param instanceof DoctorAssistant
            ? $param->user_id
            : DoctorAssistant::find($param)?->user_id;

        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:active,inactive',
            'chamber_ids' => 'required|array|min:1',
            'chamber_ids.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('chambers', 'id')->where('doctor_id', auth()->user()->doctor?->id),
            ],
        ];
    }
}
