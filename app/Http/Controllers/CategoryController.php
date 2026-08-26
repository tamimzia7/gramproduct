<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * সব ক্যাটাগরি — শুধু active
     */
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->rootLevel()
            ->ordered()
            ->withCount(['products as products_count' => fn (Builder $query) => $query->active()])
            ->with('children')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * ক্যাটাগরি পেজ — breadcrumb, সাব-ক্যাটাগরি, বংশধর-পণ্যসহ গ্রিড
     */
    public function show(Category $category): View
    {
        if (! $category->isActive()) {
            abort(404);
        }

        $category->load('children');

        // এই ক্যাটাগরি + সব বংশধর ক্যাটাগরির active পণ্য
        $categoryIds = array_merge([$category->id], $category->getDescendantIds());

        $query = Product::query()
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'primaryImage', 'images', 'activeVariants.inventory']);

        if ($search = trim((string) request('q'))) {
            $query->search($search);
        }

        match (request('sort')) {
            'price_asc' => $query->orderByRaw('COALESCE(discount_price, base_price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(discount_price, base_price) DESC'),
            'popular' => $query->orderByDesc('is_bestseller')->orderByDesc('is_featured')->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $subcategories = $category->children()
            ->active()
            ->ordered()
            ->withCount(['products as products_count' => fn (Builder $q) => $q->active()])
            ->get();

        return view('categories.show', [
            'category' => $category,
            'breadcrumb' => $category->getBreadcrumb(),
            'products' => $products,
            'subcategories' => $subcategories,
            'search' => request('q'),
        ]);
    }
}
