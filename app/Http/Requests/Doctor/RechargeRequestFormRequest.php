<?php

namespace App\Http\Requests\Doctor;

use App\Models\RechargeRequest;
use Illuminate\Foundation\Http\FormRequest;

class RechargeRequestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->doctor !== null;
    }

    protected function prepareForValidation(): void
    {
        $bn = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];
        $merge = [];

        if ($this->filled('amount')) {
            $merge['amount'] = str_replace(',', '', strtr(trim((string) $this->input('amount')), $bn));
        }

        if ($this->filled('sender_number')) {
            // "+880 1712-345678" / "8801712345678" -> "01712345678"
            $n = preg_replace('/[\s\-()]/', '', strtr((string) $this->input('sender_number'), $bn));
            $merge['sender_number'] = preg_replace('/^(\+?88)/', '', $n);
        }

        if ($this->filled('trx_id')) {
            $merge['trx_id'] = strtoupper(preg_replace('/\s+/', '', (string) $this->input('trx_id')));
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $min = (int) config('easydoc.minimum_recharge_amount', 100);

        return [
            'amount'        => ['required', 'numeric', 'min:' . $min, 'max:200000'],
            'sender_number' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'trx_id'        => [
                'required',
                'regex:/^[A-Z0-9]{8,12}$/',
                function (string $attribute, $value, \Closure $fail) {
                    $taken = RechargeRequest::where('trx_id', $value)
                        ->whereIn('status', [RechargeRequest::STATUS_PENDING, RechargeRequest::STATUS_APPROVED])
                        ->exists();

                    if ($taken) {
                        $fail('This TrxID was already submitted. Each bKash payment can be used only once.');
                    }
                },
            ],
            'doctor_note'   => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        $min = number_format((int) config('easydoc.minimum_recharge_amount', 100));

        return [
            'amount.min'          => "The minimum recharge amount is ৳{$min}.",
            'amount.numeric'      => 'Enter the amount in numbers, e.g. 1000.',
            'sender_number.regex' => 'Enter the bKash number you paid from, e.g. 01712345678.',
            'trx_id.regex'        => 'Enter the bKash TrxID exactly as in your payment SMS (8 to 12 letters and numbers).',
        ];
    }
}
