<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        // টেস্টে আসল ফাইল তৈরি হয় uploaded file দিয়ে; এখানে ডিফল্ট path
        $path = 'products/'.fake()->unique()->slug().'.jpg';

        return [
            'product_id' => Product::factory(),
            'image_path' => $path,
            'alt_text' => 'পণ্যের ছবি',
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }

    /**
     * আসল ছবি ফাইলসহ state তৈরি
     */
    public function withRealFile(string $fileName = 'test-image.jpg'): static
    {
        return $this->afterCreating(function (ProductImage $image) use ($fileName) {
            Storage::disk('public')->put(
                $image->image_path,
                UploadedFile::fake()->image($fileName)->getContent()
            );
        });
    }
}
