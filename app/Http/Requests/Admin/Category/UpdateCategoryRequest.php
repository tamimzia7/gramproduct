<?php

namespace App\Http\Requests\Admin\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,'.($category?->id ?: 'NULL')],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Category|null $category */
            $category = $this->route('category');
            $parentId = $this->input('parent_id');

            if (! $category || ! $parentId) {
                return;
            }

            if ((int) $parentId === (int) $category->id) {
                $validator->errors()->add('parent_id', 'একটি ক্যাটাগরি নিজের অভিভাবক হতে পারে না।');

                return;
            }

            if (in_array((int) $parentId, $category->getAllDescendantIds(), true)) {
                $validator->errors()->add('parent_id', 'নির্বাচিত অভিভাবক একটি গোলাকার ক্যাটাগরি শ্রেণিবিন্যাস তৈরি করবে।');
            }
        });
    }
}
