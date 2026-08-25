<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * ইউনিক slug তৈরি।
     *
     * বাংলা নাম থেকে Str::slug() খালি রিটার্ন করে, তাই fallback:
     * 1. ম্যানুয়াল slug (আগে থেকে দেওয়া থাকলে)
     * 2. নাম থেকে Latin slug
     * 3. SKU থেকে (SKU সবসময় Latin)
     * 4. 'product-' + random
     */
    public function generateUniqueSlug(string $name, ?string $manualSlug = null, ?string $sku = null, ?int $ignoreId = null): string
    {
        $base = $manualSlug
            ?: Str::slug($name)
            ?: ($sku ? Str::slug($sku) : '')
            ?: ('product-'.Str::lower(Str::random(6)));

        $slug = $base;
        $counter = 1;

        $query = Product::withTrashed()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base.'-'.$counter;
            $query = Product::withTrashed()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $counter++;
        }

        return $slug;
    }

    /**
     * একটি ছবি আপলোড করে storage/app/public/products/-এ সংরক্ষণ করা
     */
    public function storeImage(UploadedFile $file): string
    {
        return $file->store('products', 'public');
    }

    /**
     * পণ্যের জন্য ছবিগুলো sync করা:
     * - "image" (প্রধান ছবি) → is_primary = true
     * - "images[]" (অতিরিক্ত ছবি) → পরের sort order-এ
     * পণ্যে আগে কোনো primary না থাকলে প্রথম ছবিটিই primary হয়।
     */
    public function syncUploadedImages(Product $product, ?UploadedFile $mainImage, array $additionalImages = [], ?string $altText = null): void
    {
        DB::transaction(function () use ($product, $mainImage, $additionalImages, $altText) {
            $sortOrder = (int) $product->images()->max('sort_order');
            $hasPrimary = $product->images()->where('is_primary', true)->exists();

            if ($mainImage instanceof UploadedFile) {
                // প্রধান ছবি একটিই থাকবে — আগেরগুলো false
                $product->images()->update(['is_primary' => false]);

                $product->images()->create([
                    'image_path' => $this->storeImage($mainImage),
                    'alt_text' => $altText ?? $product->name,
                    'sort_order' => max(0, $sortOrder),
                    'is_primary' => true,
                ]);

                $sortOrder++;
                $hasPrimary = true;
            }

            foreach ($additionalImages as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $makePrimary = ! $hasPrimary;
                $hasPrimary = true;

                $product->images()->create([
                    'image_path' => $this->storeImage($file),
                    'alt_text' => $product->name,
                    'sort_order' => ++$sortOrder,
                    'is_primary' => $makePrimary,
                ]);
            }
        });
    }

    /**
     * একটি ছবিকে প্রধান ছবি করা — বাকিগুলোর primary মুছে যাবে
     */
    public function makePrimaryImage(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            $image->product->images()->update(['is_primary' => false]);

            // stale-model dirty-check এড়াতে query builder ব্যবহার —
            // bind-time attribute true থাকলেও update নিশ্চিতভাবে প্রয়োগ হয়
            $image->newQuery()
                ->whereKey($image->getKey())
                ->update(['is_primary' => true]);
        });
    }

    /**
     * ছবি মুছে ফেলা — ফাইলসহ। প্রধান ছবি মুছলে প্রথম বাকি ছবিটি primary হয়।
     */
    public function deleteImage(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            $wasPrimary = $image->is_primary;
            $product = $image->product;

            $image->deleteFile();
            $image->delete();

            if ($wasPrimary) {
                $replacement = $product->images()->orderBy('sort_order')->orderBy('id')->first();

                if ($replacement) {
                    $replacement->update(['is_primary' => true]);
                }
            }
        });
    }
}
