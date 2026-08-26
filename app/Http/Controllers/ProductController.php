<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * কাস্টমার-ফেসিং পণ্য তালিকা — শুধু active পণ্য
     */
    public function index(Request $request): View
    {
        $query = Product::active()
            ->with(['category', 'primaryImage', 'images', 'activeVariants.inventory']);

        // অনুসন্ধান
        $search = trim((string) $request->input('q'));
        if ($search !== '') {
            $query->search($search);
        }

        // ক্যাটাগরি ফিল্টার (slug দিয়ে — ID বাইরে থাকে)
        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $categorySlug));
        }

        // সাজানো — ডিফল্ট "প্রাসঙ্গিক" (ফিচার্ড আগে, তারপর নতুন)
        match ($request->input('sort')) {
            'price_asc' => $query->orderByRaw('COALESCE(discount_price, base_price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(discount_price, base_price) DESC'),
            'popular' => $query->orderByDesc('is_bestseller')->orderByDesc('is_featured')->latest(),
            default => $query->orderByDesc('is_featured')->orderByRaw("CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END")->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::active()->ordered()->withCount('children')->get();

        return view('products.index', compact('products', 'categories', 'search'));
    }

    /**
     * কাস্টমার-ফেসিং পণ্যের বিস্তারিত পেজ
     */
    public function show(Product $product): View
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $product->load([
            'category.parent',
            'images',
            'activeVariants.inventory',
        ]);

        // সম্পর্কিত পণ্য — একই ক্যাটাগরি, নিজেকে বাদ; স্টক-অগ্রাধিকারসহ
        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->with(['category', 'primaryImage', 'images', 'activeVariants.inventory'])
            ->orderByDesc('is_featured')
            ->orderByRaw("CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END")
            ->latest()
            ->take(4)
            ->get();

        $breadcrumb = $product->getCategoryBreadcrumb();

        return view('products.show', compact('product', 'breadcrumb', 'relatedProducts'));
    }
}
