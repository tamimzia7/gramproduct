<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ক্যাটাগরির নাম অবশ্যই দিতে হবে।',
            'name.max' => 'ক্যাটাগরির নাম সর্বাধিক ২৫৫ অক্ষরের হতে পারবে।',
            'parent_id.exists' => 'নির্বাচিত প্যারেন্ট ক্যাটাগরিটি খুঁজে পাওয়া যায়নি।',
            'slug.unique' => 'এই Slug দিয়ে ইতিমধ্যে একটি ক্যাটাগরি রয়েছে।',
            'slug.max' => 'Slug সর্বাধিক ২৫৫ অক্ষরের হতে পারবে।',
            'image.image' => 'ফাইলটি অবশ্যই একটি ছবি হতে হবে।',
            'image.mimes' => 'ছবিটি অবশ্যই JPG, JPEG, PNG অথবা WebP ফরম্যাটে হতে হবে।',
            'image.max' => 'ছবিটি সর্বোচ্চ 2MB আকারের হতে পারবে।',
            'sort_order.integer' => 'প্রদর্শনের ক্রম অবশ্যই একটি সংখ্যা হতে হবে।',
            'sort_order.min' => 'প্রদর্শনের ক্রম কমপক্ষে ০ হতে হবে।',
            'seo_title.max' => 'SEO শিরোনাম সর্বাধিক ২৫৫ অক্ষরের হতে পারবে।',
            'seo_description.max' => 'SEO বিবরণ সর্বাধিক ৫০০ অক্ষরের হতে পারবে।',
        ];
    }
}
