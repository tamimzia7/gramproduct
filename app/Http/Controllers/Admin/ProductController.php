<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {}

    /**
     * অ্যাডমিন পণ্য তালিকা
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['category', 'primaryImage']);

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

        $categories = Category::orderBy('name')->get();
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
        $categories = Category::orderBy('name')->get();
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

        return $data;
    }
}
