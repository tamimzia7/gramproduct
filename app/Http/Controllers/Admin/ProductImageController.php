<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;

class ProductImageController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {}

    /**
     * একটি ছবিকে প্রধান ছবি করা
     */
    public function makePrimary(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($image->product_id === $product->id, 404);

        $this->productService->makePrimaryImage($image);

        return back()->with('success', 'প্রধান ছবি পরিবর্তন করা হয়েছে।');
    }

    /**
     * একটি ছবি মুছে ফেলা (ফাইলসহ)
     */
    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($image->product_id === $product->id, 404);

        $this->productService->deleteImage($image);

        return back()->with('success', 'ছবিটি সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
