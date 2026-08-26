<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership service/controller-এ user scope দিয়ে নিশ্চিত
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            // বাংলাদেশ মোবাইল ফরম্যাট: 01[3-9]+৮ ডিজিট (ব্যবসায়িক গৃহীত ফরম্যাট)
            'phone' => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'division' => ['required', 'string', 'max:60', Rule::in(array_keys(config('location.divisions')))],
            // জেলা অবশ্যই নির্বাচিত বিভাগের অন্তর্ভুক্ত হতে হবে
            'district' => [
                'required',
                'string',
                'max:60',
                Rule::in(config('location.divisions.'.$this->input('division'), [])),
            ],
            'upazila' => ['required', 'string', 'max:80'],
            'area' => ['required', 'string', 'max:255'],
            'address_line' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'digits:4'],
            'delivery_note' => ['nullable', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
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
            'name' => 'নাম',
            'phone' => 'মোবাইল নম্বর',
            'division' => 'বিভাগ',
            'district' => 'জেলা',
            'upazila' => 'উপজেলা',
            'area' => 'এলাকা',
            'address_line' => 'ঠিকানা',
            'postal_code' => 'পোস্ট কোড',
            'delivery_note' => 'ডেলিভারি নির্দেশনা',
        ];
    }

    /**
     * বাংলা validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('checkout.validation.name_required'),
            'phone.required' => __('checkout.validation.phone_required'),
            'phone.regex' => __('checkout.validation.phone_invalid'),
            'division.required' => __('checkout.validation.division_required'),
            'division.in' => __('checkout.validation.division_required'),
            'district.required' => __('checkout.validation.district_required'),
            'district.in' => __('checkout.validation.district_invalid'),
            'upazila.required' => __('checkout.validation.upazila_required'),
            'area.required' => __('checkout.validation.area_required'),
            'address_line.required' => __('checkout.validation.address_required'),
            'postal_code.digits' => 'পোস্ট কোড ৪ সংখ্যার হতে হবে।',
            'delivery_note.max' => 'ডেলিভারি নির্দেশনা সর্বোচ্চ ৫০০ অক্ষরের হতে পারে।',
        ];
    }

    /**
     * validated data — is_default boolean normalize
     *
     * @return array<string, mixed>
     */
    public function validatedWithFlags(): array
    {
        $data = $this->validated();
        $data['is_default'] = $this->boolean('is_default');

        return $data;
    }
}
