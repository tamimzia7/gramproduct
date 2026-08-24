<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-categories');

        $categories = Category::getFlatTree();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        Gate::authorize('manage-categories');

        $parentOptions = Category::getFlatTree();

        return view('admin.categories.create', compact('parentOptions'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('manage-categories');

        $data = $request->safe()->except(['image']);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('status', 'ক্যাটাগরি সফলভাবে তৈরি করা হয়েছে।');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('manage-categories');

        $excluded = array_merge([$category->id], $category->getAllDescendantIds());
        $parentOptions = Category::getFlatTree()->reject(fn (Category $c) => in_array($c->id, $excluded, true));

        return view('admin.categories.edit', compact('category', 'parentOptions'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('manage-categories');

        $data = $request->safe()->except(['image']);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('status', 'ক্যাটাগরি সফলভাবে আপডেট করা হয়েছে।');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('manage-categories');

        if ($category->hasChildren()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'উপ-ক্যাটাগরি থাকলে ক্যাটাগরি মুছে ফেলা যাবে না। প্রথমে উপ-ক্যাটাগরি সরিয়ে ফেলুন বা মুছে ফেলুন।');
        }

        if (class_exists(Product::class) && $category->products()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'পণ্য থাকলে ক্যাটাগরি মুছে ফেলা যাবে না। প্রথমে পণ্য সরিয়ে ফেলুন।');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', 'ক্যাটাগরি সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
