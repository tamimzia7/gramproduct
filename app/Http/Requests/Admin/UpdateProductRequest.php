<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductUnit;
use App\Enums\StockStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:base_price'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'unit' => ['nullable', Rule::in(ProductUnit::values())],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:500'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'product_type' => ['nullable', 'string', 'max:50'],
            'stock_status' => ['required', Rule::in(StockStatus::values())],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'is_new_arrival' => ['boolean'],
            'is_seasonal' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'origin' => ['nullable', 'string', 'max:255'],
            'farmer_name' => ['nullable', 'string', 'max:255'],
            'seasonal_info' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'name' => 'পণ্যের নাম',
            'name_bn' => 'পণ্যের বাংলা নাম',
            'sku' => 'SKU',
            'slug' => 'স্লাগ',
            'category_id' => 'ক্যাটাগরি',
            'short_description' => 'সংক্ষিপ্ত বিবরণ',
            'description' => 'বিস্তারিত বিবরণ',
            'base_price' => 'মূল্য',
            'discount_price' => 'বিক্রয় মূল্য',
            'compare_at_price' => 'আগের মূল্য',
            'unit' => 'একক',
            'weight' => 'ওজন',
            'brand' => 'ব্র্যান্ড',
            'tags' => 'ট্যাগ',
            'low_stock_threshold' => 'মজুদের সতর্কতা সীমা',
            'product_type' => 'পণ্যের ধরন',
            'stock_status' => 'স্টক স্ট্যাটাস',
            'sort_order' => 'প্রদর্শনের ক্রম',
            'origin' => 'উৎস',
            'farmer_name' => 'কৃষকের নাম',
            'seasonal_info' => 'মৌসুমি তথ্য',
            'seo_title' => 'SEO শিরোনাম',
            'seo_description' => 'SEO বিবরণ',
            'image' => 'প্রধান ছবি',
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
            'name.required' => 'পণ্যের নাম লিখুন।',
            'sku.unique' => 'এই SKU ইতোমধ্যে ব্যবহৃত হয়েছে।',
            'slug.unique' => 'এই স্লাগ ইতোমধ্যে ব্যবহৃত হয়েছে।',
            'category_id.required' => 'ক্যাটাগরি নির্বাচন করুন।',
            'category_id.exists' => 'নির্বাচিত ক্যাটাগরি সঠিক নয়।',
            'base_price.required' => 'পণ্যের মূল্য লিখুন।',
            'base_price.numeric' => 'মূল্য অবশ্যই একটি সংখ্যা হতে হবে।',
            'base_price.min' => 'মূল্য শূন্যের কম হতে পারবে না।',
            'stock_status.required' => 'স্টক স্ট্যাটাস নির্বাচন করুন।',
            'stock_status.in' => 'স্টক স্ট্যাটাস সঠিক নয়।',
            'image.image' => 'অনুগ্রহ করে একটি বৈধ ছবি নির্বাচন করুন।',
            'image.mimes' => 'ছবিটি অবশ্যই JPG, JPEG, PNG অথবা WebP ফরম্যাটে হতে হবে।',
            'image.max' => 'ছবির আকার সর্বোচ্চ 2MB হতে পারবে।',
            'images.max' => 'সর্বোচ্চ ৫টি অতিরিক্ত ছবি যোগ করা যাবে।',
            'images.*.image' => 'অনুগ্রহ করে বৈধ ছবি নির্বাচন করুন।',
            'images.*.mimes' => 'ছবিটি অবশ্যই JPG, JPEG, PNG অথবা WebP ফরম্যাটে হতে হবে।',
            'images.*.max' => 'ছবির আকার সর্বোচ্চ 2MB হতে পারবে।',
        ];
    }
}
