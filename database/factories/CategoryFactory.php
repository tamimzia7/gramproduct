<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'image' => null,
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'sort_order' => 0,
            'seo_title' => null,
            'seo_description' => null,
        ];
    }
}
