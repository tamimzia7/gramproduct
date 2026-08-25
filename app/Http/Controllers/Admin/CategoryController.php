<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService,
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $query = Category::with('parent');

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        // Filter by parent
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $parentCategories = $this->categoryService->getSelectableCategories();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // হায়ারার্কি যাচাই
        if (! $this->categoryService->validateHierarchy(
            $validated['parent_id'] ?? null,
        )) {
            return back()
                ->withErrors(['parent_id' => 'নিজের বা নিজের সাব-ক্যাটাগরিকে প্যারেন্ট হিসেবে নির্ধারণ করা যাবে না।'])
                ->withInput();
        }

        // Slug তৈরি
        $validated['slug'] = $this->categoryService->generateUniqueSlug(
            $validated['name']
        );

        // ছবি আপলোড হ্যান্ডল করা
        if ($request->hasFile('image')) {
            $validated['image'] = $this->categoryService->handleImageUpload(
                $request->file('image')
            );
        }

        // Set defaults
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Remove image from validated if null
        if (! isset($validated['image'])) {
            unset($validated['image']);
        }

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'ক্যাটাগরি সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): View
    {
        $category->load(['parent', 'children', 'products']);

        $breadcrumb = $category->getBreadcrumb();

        return view('admin.categories.show', compact('category', 'breadcrumb'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $parentCategories = $this->categoryService->getSelectableCategories(
            excludeId: $category->id
        );

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        // হায়ারার্কি যাচাই
        $parentId = $validated['parent_id'] ?? null;
        if (! $this->categoryService->validateHierarchy($parentId, $category->id)) {
            return back()
                ->withErrors(['parent_id' => 'নিজের বা নিজের সাব-ক্যাটাগরিকে প্যারেন্ট হিসেবে নির্ধারণ করা যাবে না।'])
                ->withInput();
        }

        // নাম পরিবর্তন হলে বা slug খালি থাকলে নতুন slug তৈরি
        if (
            empty($validated['slug']) ||
            $validated['name'] !== $category->name
        ) {
            $validated['slug'] = $this->categoryService->generateUniqueSlug(
                $validated['name'],
                $category->id
            );
        }

        // ছবি আপলোড হ্যান্ডল করা
        if ($request->hasFile('image')) {
            // পুরনো ছবি মুছে ফেলা
            $this->categoryService->deleteImage($category->image);

            $validated['image'] = $this->categoryService->handleImageUpload(
                $request->file('image')
            );
        } else {
            unset($validated['image']);
        }

        // Set boolean values
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'ক্যাটাগরি সফলভাবে আপডেট হয়েছে।');
    }

    /**
     * Remove the specified category (soft delete).
     */
    public function destroy(Category $category): RedirectResponse
    {
        // সাব-ক্যাটাগরি থাকলে মুছে ফেলা যাবে না
        if ($category->hasChildren()) {
            return back()
                ->with('error', 'যে ক্যাটাগরিতে সাব-ক্যাটাগরি আছে তা মুছে ফেলা যাবে না। প্রথমে সাব-ক্যাটাগরিগুলো অন্য ক্যাটাগরিতে সরিয়ে ফেলুন।');
        }

        // পণ্য থাকলে মুছে ফেলা যাবে না
        if ($category->products()->exists()) {
            return back()
                ->with('error', 'যে ক্যাটাগরিতে পণ্য আছে তা মুছে ফেলা যাবে না। প্রথমে পণ্যগুলো অন্য ক্যাটাগরিতে সরিয়ে ফেলুন।');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'ক্যাটাগরি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restore(int $id): RedirectResponse
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'ক্যাটাগরি সফলভাবে পুনরুদ্ধার হয়েছে।');
    }
}
