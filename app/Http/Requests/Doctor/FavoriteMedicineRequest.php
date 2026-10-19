<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FavoriteMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->doctor !== null;
    }

    protected function prepareForValidation(): void
    {
        $bn = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];

        $dose = trim(strtr((string) $this->input('default_dose'), $bn));
        $dose = preg_replace('/\s+/', '', $dose);

        $this->merge([
            'is_favorite'  => $this->boolean('is_favorite'),
            'is_default'   => $this->boolean('is_default'),
            'default_dose' => $dose === '' ? null : $dose,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'is_favorite' => ['boolean'],
            'is_default'  => ['boolean'],

            // 1, 1+0+1, 0.5+0+0.5 (same shape the prescription form accepts)
            'default_dose'        => ['nullable', 'regex:/^\d+(\.\d)?(\+\d+(\.\d)?){0,2}$/'],
            'default_dose_text'   => ['nullable', 'string', 'max:120'],
            'default_duration'    => ['nullable', 'string', 'max:120'],
            'default_instruction' => ['nullable', 'string', 'max:255'],
        ];

        // The medicine is chosen only when adding; editing keeps the same medicine
        if ($this->isMethod('post')) {
            $rules['medicine_id'] = ['required', 'integer', 'exists:medicines,id'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->boolean('is_default')
                && blank($this->input('default_dose'))
                && blank($this->input('default_dose_text'))) {
                $v->errors()->add('default_dose', 'Write the dose to add this medicine automatically (e.g. 1+0+1).');
            }

            if (! $this->boolean('is_default') && ! $this->boolean('is_favorite')) {
                $v->errors()->add('is_favorite', 'Choose at least one: Favorite or Add automatically.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'medicine_id.required' => 'Select a medicine from the list.',
            'medicine_id.exists'   => 'Select a medicine from the list.',
            'default_dose.regex'   => 'Dose looks like 1+0+1, 1+1+1 or 0.5+0+0.5.',
        ];
    }
}
