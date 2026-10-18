<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

        return [

            'name'=>'required|string|max:255',

            'email'=>'required|email',

            'phone'=>'required|string|max:20',

            'bmdc_number'=>'nullable|string',

            'speciality'=>'nullable|string',

            'qualification'=>'nullable|string',

            'designation'=>'nullable|string',

            'experience'=>'nullable|string',

            'gender'=>'nullable|string',

            'photo'=>'nullable|image|max:2048'

        ];

    }

}