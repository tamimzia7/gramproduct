<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function index(Product $product): View
    {
        Gate::authorize('manage-products');

        $variants = $product->variants()->ordered()->get();

        return view('admin.products.variants.index', compact('product', 'variants'));
    }

    public function create(Product $product): View
    {
        Gate::authorize('manage-products');

        return view('admin.products.variants.create', compact('product'));
    }

    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('manage-products');

        $product->variants()->create($request->validated());

        return redirect()->route('admin.products.variants.index', $product)
            ->with('status', 'ভ্যারিয়েন্ট সফলভাবে তৈরি করা হয়েছে।');
    }

    public function edit(Product $product, ProductVariant $variant): View
    {
        Gate::authorize('manage-products');

        abort_unless($variant->product_id === $product->id, 404);

        return view('admin.products.variants.edit', compact('product', 'variant'));
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        Gate::authorize('manage-products');

        abort_unless($variant->product_id === $product->id, 404);

        $variant->update($request->validated());

        return redirect()->route('admin.products.variants.index', $product)
            ->with('status', 'ভ্যারিয়েন্ট সফলভাবে আপডেট করা হয়েছে।');
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        Gate::authorize('manage-products');

        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return redirect()->route('admin.products.variants.index', $product)
            ->with('status', 'ভ্যারিয়েন্ট সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
