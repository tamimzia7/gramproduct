<?php

namespace App\Http\Requests\Admin\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'minimum_order' => ['nullable', 'integer', 'min:1'],
            'maximum_order' => ['nullable', 'integer', 'min:1', 'gte:minimum_order'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
