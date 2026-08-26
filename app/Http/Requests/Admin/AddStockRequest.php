<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * বাংলা attribute — validation message-এ :attribute হিসেবে দেখাবে
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'quantity' => 'যোগ করার পরিমাণ',
            'note' => 'নোট',
        ];
    }

    /**
     * গুরুত্বপূর্ণ নিয়মের বাংলা মেসেজ; বাকিগুলো lang/bn/validation.php থেকে আসে
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'যোগ করার পরিমাণ লিখুন।',
            'quantity.integer' => 'পরিমাণ অবশ্যই পূর্ণসংখ্যা হতে হবে।',
            'quantity.min' => 'পরিমাণ অন্তত ১টি হতে হবে।',
        ];
    }
}
