<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'type'     => ['required', Rule::in(array_keys(Announcement::TYPES))],
            'title'    => ['required', 'string', 'max:160'],
            'body'     => ['required', 'string', 'min:5', 'max:2000'],
            'link_url' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, $value, \Closure $fail) {
                    // Only a full http(s) address or a path inside the site
                    if (! preg_match('#^(https?://|/)#i', $value) || preg_match('#^//#', $value)) {
                        $fail('Enter a full link starting with http:// or https://, or a path starting with /.');
                    }
                },
            ],
        ];
    }
}
