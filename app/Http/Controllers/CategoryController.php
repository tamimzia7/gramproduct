<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->active()->ordered()])
            ->ordered()
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        if (! $category->isActive()) {
            abort(404);
        }

        $category->load(['children' => fn ($query) => $query->active()->ordered()]);

        $title = $category->seo_title ?: $category->name;
        $metaDescription = $category->seo_description;

        return view('categories.show', compact('category', 'title', 'metaDescription'));
    }
}
