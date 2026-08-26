<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function __construct(
        private ProductVariantService $variantService,
    ) {}

    /**
     * নতুন ভ্যারিয়েন্ট তৈরির ফর্ম
     */
    public function create(Product $product): View
    {
        $this->authorize('create', ProductVariant::class);

        return view('admin.products.variants.create', [
            'product' => $product,
            'units' => ProductUnit::cases(),
        ]);
    }

    /**
     * নতুন ভ্যারিয়েন্ট সংরক্ষণ
     */
    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('create', ProductVariant::class);

        $validated = $this->prepareData($request);

        $variant = $this->variantService->create($product, $validated);

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'ভ্যারিয়েন্টটি সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    /**
     * ভ্যারিয়েন্ট সম্পাদনার ফর্ম
     */
    public function edit(Product $product, ProductVariant $variant): View
    {
        $this->authorize('update', $variant);

        abort_unless($variant->product_id === $product->id, 404);

        return view('admin.products.variants.edit', [
            'product' => $product,
            'variant' => $variant,
            'units' => ProductUnit::cases(),
        ]);
    }

    /**
     * ভ্যারিয়েন্ট আপডেট
     */
    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant);

        abort_unless($variant->product_id === $product->id, 404);

        $this->variantService->update($variant, $this->prepareData($request));

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'ভ্যারিয়েন্টটি সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * ভ্যারিয়েন্ট soft-delete — ডিফল্ট হলে fallback ডিফল্ট বসে
     */
    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('delete', $variant);

        abort_unless($variant->product_id === $product->id, 404);

        $this->variantService->delete($variant);

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'ভ্যারিয়েন্টটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * ডিফল্ট ভ্যারিয়েন্ট নির্ধারণ
     */
    public function setDefault(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant);

        abort_unless($variant->product_id === $product->id, 404);
        abort_unless($variant->isActive(), 422, 'নিষ্ক্রিয় ভ্যারিয়েন্টকে ডিফল্ট করা যাবে না।');

        $this->variantService->setDefault($variant);

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'ডিফল্ট ভ্যারিয়েন্ট নির্ধারণ করা হয়েছে।');
    }

    /**
     * সক্রিয় / নিষ্ক্রিয় toggle
     */
    public function toggleActive(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $variant);

        abort_unless($variant->product_id === $product->id, 404);

        $this->variantService->setActive($variant, ! $variant->isActive());

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', $variant->isActive() ? 'ভ্যারিয়েন্টটি সক্রিয় করা হয়েছে।' : 'ভ্যারিয়েন্টটি নিষ্ক্রিয় করা হয়েছে।');
    }

    /**
     * Request ডেটা থেকে service-এ পাঠানোর উপযোগী array তৈরি
     *
     * @return array<string, mixed>
     */
    private function prepareData(StoreProductVariantRequest|UpdateProductVariantRequest $request): array
    {
        $data = $request->safe()->except(['product_id']);

        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
