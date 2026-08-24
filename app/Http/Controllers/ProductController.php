<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::active()
            ->with(['category', 'variants' => function ($query) {
                $query->active();
            }])
            ->search(request('search'))
            ->when(request('category_id'), fn ($q) => $q->where('category_id', request('category_id')))
            ->when(request('sort') === 'price_asc', fn ($q) => $q->orderBy('base_price', 'asc'))
            ->when(request('sort') === 'price_desc', fn ($q) => $q->orderBy('base_price', 'desc'))
            ->when(request('sort') === 'newest', fn ($q) => $q->latest())
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->active()->ordered()])
            ->ordered()
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $product->load(['category', 'variants' => function ($query) {
            $query->active()->ordered();
        }]);

        $breadcrumb = $product->getCategoryBreadcrumb();

        $title = $product->seo_title ?: $product->name;
        $metaDescription = $product->seo_description;

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'breadcrumb', 'title', 'metaDescription', 'relatedProducts'));
    }
}
