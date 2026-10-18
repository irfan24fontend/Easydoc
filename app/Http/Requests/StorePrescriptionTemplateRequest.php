<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => 'required|max:255',

            'paper_size' => 'required|in:A4,A5',

            'html' => 'required',

            'css' => 'nullable',

            'status' => 'required|boolean',

        ];
    }
}