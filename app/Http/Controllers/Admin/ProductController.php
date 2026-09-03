<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private CategoryService $categoryService,
    ) {}

    /**
     * অ্যাডমিন পণ্য তালিকা
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['category', 'primaryImage', 'activeVariants.inventory']);

        // নাম বা SKU দিয়ে খোঁজা
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('featured')) {
            $query->featured();
        }

        if ($request->filled('bestseller')) {
            $query->bestseller();
        }

        if ($request->filled('stock')) {
            $stock = $request->input('stock');
            if ($stock === 'in_stock') {
                $query->where('stock_status', 'in_stock');
            } elseif ($stock === 'out_of_stock') {
                $query->where('stock_status', 'out_of_stock');
            }
        }

        // মুছে ফেলা পণ্য দেখানোর অপশন
        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        $products = $query->ordered()
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * নতুন পণ্য তৈরির ফর্ম
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = $this->categoryService->getHierarchicalCategories();
        $units = ProductUnit::cases();

        return view('admin.products.create', compact('categories', 'units'));
    }

    /**
     * নতুন পণ্য সংরক্ষণ
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $this->prepareData($request);

        $validated['slug'] = $this->productService->generateUniqueSlug(
            $request->input('name'),
            $request->input('slug'),
            $request->input('sku'),
        );

        $product = DB::transaction(function () use ($request, $validated) {
            $product = Product::create($validated);

            $this->productService->syncUploadedImages(
                $product,
                $request->file('image'),
                (array) $request->file('images', []),
                $request->input('image_alt_text'),
            );

            return $product;
        });

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'পণ্যটি সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    /**
     * পণ্যের বিস্তারিত (অ্যাডমিন)
     */
    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load([
            'category.parent',
            'images',
            'variants' => fn ($query) => $query->ordered(),
        ]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * পণ্য সম্পাদনার ফর্ম
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('images');
        $categories = $this->categoryService->getHierarchicalCategories();
        $units = ProductUnit::cases();

        return view('admin.products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * পণ্য আপডেট
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $this->prepareData($request);

        // ম্যানুয়াল slug দেওয়া থাকলে বা বর্তমান slug ফাঁকা হলে নতুন করে তৈরি হবে;
        // slug না বদলালে পুরনো URL অপরিবর্তিত থাকে।
        $manualSlug = $request->filled('slug') && $request->input('slug') !== $product->slug
            ? $request->input('slug')
            : null;

        if ($manualSlug !== null || empty($product->slug)) {
            $validated['slug'] = $this->productService->generateUniqueSlug(
                $request->input('name'),
                $manualSlug,
                $request->input('sku'),
                $product->id,
            );
        } else {
            unset($validated['slug']);
        }

        DB::transaction(function () use ($request, $product, $validated) {
            $product->update($validated);

            $this->productService->syncUploadedImages(
                $product,
                $request->file('image'),
                (array) $request->file('images', []),
                $request->input('image_alt_text'),
            );
        });

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'পণ্যটি সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * পণ্য soft-delete
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'পণ্যটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * মুছে ফেলা পণ্য পুনরুদ্ধার
     */
    public function restore(int $id): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'পণ্যটি সফলভাবে পুনরুদ্ধার হয়েছে।');
    }

    /**
     * বাল্ক অ্যাকশন — সক্রিয়/নিষ্ক্রিয়/মুছে ফেলা
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorize('delete', Product::class);

        $request->validate([
            'ids' => ['required', 'array'],
            'action' => ['required', 'in:activate,deactivate,delete'],
        ], [
            'ids.required' => 'অনুগ্রহ করে অন্তত একটি পণ্য নির্বাচন করুন।',
            'ids.array' => 'নির্বাচিত পণ্যগুলো সঠিক নয়।',
            'action.required' => 'অ্যাকশন নির্বাচন করুন।',
            'action.in' => 'অ্যাকশন সঠিক নয়।',
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        $message = match ($action) {
            'activate' => Product::whereIn('id', $ids)->update(['is_active' => true])
                ? count($ids).'টি পণ্য সক্রিয় করা হয়েছে।'
                : 'পণ্য সক্রিয় করা যায়নি।',
            'deactivate' => Product::whereIn('id', $ids)->update(['is_active' => false])
                ? count($ids).'টি পণ্য নিষ্ক্রিয় করা হয়েছে।'
                : 'পণ্য নিষ্ক্রিয় করা যায়নি।',
            'delete' => Product::whereIn('id', $ids)->delete()
                ? count($ids).'টি পণ্য মুছে ফেলা হয়েছে।'
                : 'পণ্য মুছে ফেলা যায়নি。',
            default => 'অজানা অ্যাকশন।',
        };

        return redirect()->route('admin.products.index')->with('success', $message);
    }

    /**
     * Request ডেটা থেকে model-এ লেখার উপযোগী array তৈরি
     *
     * @return array<string, mixed>
     */
    private function prepareData(StoreProductRequest|UpdateProductRequest $request): array
    {
        $data = $request->safe()->except(['image', 'images', 'image_alt_text']);

        foreach (['is_active', 'is_featured', 'is_bestseller', 'is_new_arrival', 'is_seasonal'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 5;

        return $data;
    }
}
