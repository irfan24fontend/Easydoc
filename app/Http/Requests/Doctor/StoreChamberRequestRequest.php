<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreChamberRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'chamber_name'    => ['required', 'string', 'max:255'],
            'hospital_name'   => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:255'],
            'address'         => ['required', 'string', 'max:1000'],
            'visiting_days'   => ['required', 'array', 'min:1'],
            'visiting_days.*' => ['string', 'in:শনি,রবি,সোম,মঙ্গল,বুধ,বৃহস্পতি,শুক্র'],
            'visiting_time'   => ['required', 'string', 'max:100'],
            'pad_image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'chamber_name'  => 'চেম্বারের নাম',
            'hospital_name' => 'হাসপাতাল/ক্লিনিকের নাম',
            'phone'         => 'ফোন নম্বর',
            'address'       => 'ঠিকানা',
            'visiting_days' => 'বসার দিন',
            'visiting_time' => 'বসার সময়',
            'pad_image'     => 'প্রেসক্রিপশন প্যাডের ছবি',
        ];
    }
}
