<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'image' => null,
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
            'is_featured' => false,
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
}
