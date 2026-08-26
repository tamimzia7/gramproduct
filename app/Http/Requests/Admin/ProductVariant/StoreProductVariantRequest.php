<?php

namespace App\Http\Requests\Admin\ProductVariant;

use App\Enums\ProductUnit;
use App\Enums\StockStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:product_variants,sku'],
            'unit' => ['required', Rule::in(ProductUnit::values())],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:price'],
            'stock_status' => ['required', Rule::in(StockStatus::values())],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'product_id' => 'পণ্য',
            'name' => 'ভ্যারিয়েন্টের নাম',
            'sku' => 'SKU',
            'unit' => 'একক',
            'quantity' => 'পরিমাণ',
            'price' => 'মূল্য',
            'compare_at_price' => 'আগের মূল্য',
            'stock_status' => 'স্টক স্ট্যাটাস',
            'is_default' => 'ডিফল্ট ভ্যারিয়েন্ট',
            'is_active' => 'সক্রিয়',
            'sort_order' => 'প্রদর্শনের ক্রম',
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
            'name.required' => 'ভ্যারিয়েন্টের নাম লিখুন।',
            'sku.required' => 'SKU লিখুন।',
            'sku.unique' => 'এই SKU ইতোমধ্যে ব্যবহৃত হয়েছে।',
            'unit.required' => 'একটি বৈধ একক নির্বাচন করুন।',
            'unit.in' => 'একটি বৈধ একক নির্বাচন করুন।',
            'quantity.required' => 'পরিমাণ লিখুন।',
            'quantity.gt' => 'পরিমাণ শূন্যের বেশি হতে হবে।',
            'price.required' => 'মূল্য লিখুন।',
            'price.min' => 'মূল্য শূন্যের কম হতে পারবে না।',
            'compare_at_price.gte' => 'আগের মূল্য বর্তমান মূল্যের সমান বা বেশি হতে হবে।',
            'compare_at_price.min' => 'আগের মূল্য শূন্যের কম হতে পারবে না।',
            'stock_status.required' => 'একটি বৈধ স্ট্যাটাস নির্বাচন করুন।',
            'stock_status.in' => 'একটি বৈধ স্ট্যাটাস নির্বাচন করুন।',
            'product_id.exists' => 'নির্বাচিত পণ্যটি সঠিক নয়।',
        ];
    }
}
