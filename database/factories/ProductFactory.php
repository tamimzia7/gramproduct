<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        // ডেমো/টেস্ট ডেটা বাংলায় — English business name seed নিষিদ্ধ
        $name = 'পণ্য '.fake()->unique()->numberBetween(1, 999999);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'sku' => strtoupper(fake()->unique()->bothify('??-#####')),
            'slug' => 'product-'.fake()->unique()->slug(),
            'short_description' => 'গ্রাম থেকে সংগৃহীত উন্নত মানের পণ্য।',
            'description' => 'সরাসরি কৃষকের কাছ থেকে সংগ্রহ করা খাঁটি ও টাটকা পণ্য।',
            'image' => null,
            'base_price' => fake()->randomFloat(2, 10, 500),
            'discount_price' => null,
            'compare_at_price' => null,
            'unit' => fake()->randomElement(['kg', 'gram', 'liter', 'piece', 'pack']),
            'product_type' => 'physical',
            'is_featured' => false,
            'is_bestseller' => false,
            'is_new_arrival' => false,
            'is_seasonal' => false,
            'is_active' => true,
            'stock_status' => 'in_stock',
            'sort_order' => 0,
            'origin' => null,
            'farmer_name' => null,
            'seasonal_info' => null,
            'seo_title' => null,
            'seo_description' => null,
        ];
    }

    /**
     * নিষ্ক্রিয় পণ্য state
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
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
}
