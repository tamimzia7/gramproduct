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
        $weight = fake()->randomElement([0.5, 1, 2, 5, 10, 25]);

        return [
            'product_id' => Product::factory(),
            'name' => "{$weight} কেজি",
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'weight' => $weight,
            'unit' => 'কেজি',
            'price' => fake()->randomFloat(2, 50, 5000),
            'discount_price' => null,
            'minimum_order' => 1,
            'maximum_order' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withDiscount(?float $discount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => $discount ?? max(1, $attributes['price'] - 10),
        ]);
    }
}
