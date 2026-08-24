<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-products');

        $products = Product::with('category')
            ->withCount('variants')
            ->search(request('search'))
            ->when(request('category_id'), fn ($q) => $q->where('category_id', request('category_id')))
            ->when(request('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when(request('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when(request('flag') === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when(request('flag') === 'bestseller', fn ($q) => $q->where('is_bestseller', true))
            ->when(request('flag') === 'new_arrival', fn ($q) => $q->where('is_new_arrival', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Category::getFlatTree();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        Gate::authorize('manage-products');

        $categories = Category::active()->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Gate::authorize('manage-products');

        $data = $request->safe()->except(['image']);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('status', 'পণ্য সফলভাবে তৈরি করা হয়েছে।');
    }

    public function edit(Product $product): View
    {
        Gate::authorize('manage-products');

        $categories = Category::active()->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('manage-products');

        $data = $request->safe()->except(['image']);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('status', 'পণ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('manage-products');

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('status', 'পণ্য সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
