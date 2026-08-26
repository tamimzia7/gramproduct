<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => '১ কেজি',
            'sku' => strtoupper(fake()->unique()->bothify('VAR-??-####')),
            'unit' => 'kg',
            'quantity' => 1,
            'price' => fake()->randomFloat(2, 10, 500),
            'compare_at_price' => null,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * নিষ্ক্রিয় ভ্যারিয়েন্ট state
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * ডিফল্ট ভ্যারিয়েন্ট state
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * স্টক শেষ state
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_status' => 'out_of_stock',
        ]);
    }

    /**
     * প্রি-অর্ডার state
     */
    public function preOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_status' => 'pre_order',
        ]);
    }
}
