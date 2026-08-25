<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->unique()->words(2, true),
            'sku' => strtoupper(fake()->unique()->bothify('??-#####')),
            'weight' => fake()->randomFloat(2, 1, 20),
            'unit' => fake()->randomElement(['kg', 'piece', 'liter']),
            'price' => fake()->randomFloat(2, 10, 500),
            'discount_price' => null,
            'minimum_order' => 1,
            'maximum_order' => 0,
            'is_active' => true,
        ];
    }
}
