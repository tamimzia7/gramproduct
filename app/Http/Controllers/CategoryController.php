<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()->ordered()->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        if (! $category->is_active) {
            abort(404);
        }

        $products = $category->products()->active()
            ->with(['category', 'primaryImage', 'images', 'activeVariants.inventory'])
            ->latest()
            ->paginate(12);

        return view('categories.show', compact('category', 'products'));
    }
}
