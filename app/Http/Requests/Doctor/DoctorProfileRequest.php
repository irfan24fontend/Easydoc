<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->doctor !== null;
    }

    protected function prepareForValidation(): void
    {
        // Drop completely empty repeater rows so "add row" buttons left blank do not fail validation.
        $clean = function ($rows, array $keys) {
            if (! is_array($rows)) {
                return [];
            }

            return collect($rows)
                ->filter(fn ($row) => is_array($row)
                    && collect($keys)->contains(fn ($k) => trim((string) ($row[$k] ?? '')) !== ''))
                ->values()
                ->all();
        };

        $this->merge([
            'education'       => $clean($this->input('education'), ['degree', 'institution', 'year']),
            'work_experience' => $clean($this->input('work_experience'), ['position', 'organization', 'from', 'to']),
            'show_contact'    => $this->boolean('show_contact'),
            'is_published'    => $this->has('is_published') ? $this->boolean('is_published') : true,
        ]);

        if ($this->filled('slug')) {
            $this->merge(['slug' => strtolower(trim((string) $this->input('slug')))]);
        }
    }

    public function rules(): array
    {
        $year = (int) date('Y');
        $creating = $this->isMethod('post');

        $rules = [
            'tagline' => ['required', 'string', 'max:160'],
            'about'   => ['required', 'string', 'min:80', 'max:3000'],

            'education'               => ['required', 'array', 'min:1', 'max:10'],
            'education.*.degree'      => ['required', 'string', 'max:120'],
            'education.*.institution' => ['required', 'string', 'max:160'],
            'education.*.year'        => ['nullable', 'integer', 'between:1950,' . $year],

            'work_experience'                => ['nullable', 'array', 'max:15'],
            'work_experience.*.position'     => ['required', 'string', 'max:120'],
            'work_experience.*.organization' => ['required', 'string', 'max:160'],
            'work_experience.*.from'         => ['nullable', 'integer', 'between:1950,' . $year],
            'work_experience.*.to'           => ['nullable', 'integer', 'between:1950,' . $year],

            // comma separated in the form, converted to arrays by the controller
            'expertise' => ['required', 'string', 'max:600'],
            'languages' => ['nullable', 'string', 'max:200'],

            'consultation_fee' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'website_url'      => ['nullable', 'url', 'max:200'],
            'facebook_url'     => ['nullable', 'url', 'max:200'],

            'show_contact' => ['boolean'],
            'is_published' => ['boolean'],

            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($creating) {
            // The link is chosen once; after that it is locked so shared links never break.
            $rules['slug'] = [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('doctor_profiles', 'slug'),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'slug.regex'                  => 'Use only small letters, numbers and hyphens, e.g. dr-rahim-ahmed.',
            'slug.unique'                 => 'This profile link is already taken. Try another one.',
            'about.min'                   => 'Please write at least 80 characters about yourself.',
            'education.required'          => 'Add at least one degree.',
            'education.min'               => 'Add at least one degree.',
            'education.*.degree.required' => 'Each education row needs a degree.',
            'education.*.institution.required' => 'Each education row needs an institution.',
            'expertise.required'          => 'Add at least one area of expertise.',
        ];
    }
}
