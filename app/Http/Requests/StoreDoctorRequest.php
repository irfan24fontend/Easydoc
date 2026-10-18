<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],

            'phone' => [
                'required',
                'string',
                'max:20'
            ],

            'password' => [
                'required',
                'min:8'
            ],

            'bmdc_number' => [
                'nullable',
                'unique:doctors,bmdc_number'
            ],

            'speciality' => [
                'nullable',
                'string'
            ],

            'qualification' => [
                'nullable',
                'string'
            ],

            'designation' => [
                'nullable',
                'string'
            ],

            'experience' => [
                'nullable',
                'string'
            ],

            'gender' => [
                'nullable',
                'string'
            ],

            'photo' => [
                'nullable',
                'image',
                'max:2048'
            ],

        ];
    }
}