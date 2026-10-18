<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_name'       => 'required|max:255',
            'generic_name'     => 'required|max:255',
            'strength'         => 'nullable|max:100',
            'company'          => 'nullable|max:255',
            'dosage_form'      => 'required|in:Tablet,Capsule,Syrup,Injection,Drop,Cream,Inhaler,Other',
            'category'         => 'nullable|max:255',
            'shortcut'         => 'nullable|max:50|unique:medicines,shortcut,' . $this->medicine->id,
            'status'           => 'nullable|boolean',
            'is_sponsored'     => 'nullable|boolean',
            'sponsor_company'  => 'nullable|max:255|required_if:is_sponsored,1',
            'sponsor_priority' => 'nullable|integer|min:0',
        ];
    }
}