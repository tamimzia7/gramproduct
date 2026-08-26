<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // স্বাক্ষরিত ডেল্টা — +৫ / -৫; ০ গ্রহণযোগ্য নয়
            'quantity' => ['required', 'integer', 'not_in:0', 'gte:-1000000', 'lte:1000000'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
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
            'quantity' => 'সমন্বয়ের পরিমাণ',
            'reason' => 'কারণ',
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
            'quantity.required' => 'সমন্বয়ের পরিমাণ লিখুন।',
            'quantity.integer' => 'পরিমাণ অবশ্যই পূর্ণসংখ্যা হতে হবে।',
            'quantity.not_in' => 'সমন্বয়ের পরিমাণ শূন্য হতে পারবে না।',
            'reason.required' => 'সমন্বয়ের কারণ লিখুন।',
            'reason.min' => 'কারণ অন্তত ৩ অক্ষরের হতে হবে।',
        ];
    }
}
