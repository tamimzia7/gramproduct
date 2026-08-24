<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'slug' => fake()->unique()->slug(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'image' => null,
            'base_price' => fake()->randomFloat(2, 10, 5000),
            'discount_price' => null,
            'unit' => 'কেজি',
            'product_type' => 'সাধারণ',
            'is_featured' => false,
            'is_bestseller' => false,
            'is_new_arrival' => false,
            'is_active' => true,
            'seo_title' => null,
            'seo_description' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function bestseller(): static
    {
        return $this->state(fn () => ['is_bestseller' => true]);
    }

    public function newArrival(): static
    {
        return $this->state(fn () => ['is_new_arrival' => true]);
    }

    public function withDiscount(?float $discount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => $discount ?? max(1, $attributes['base_price'] - 10),
        ]);
    }
}
