<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\AdjustStockRequest;
use App\Http\Requests\Admin\Inventory\StoreInventoryRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    public function index(): View
    {
        Gate::authorize('manage-inventory');

        $inventories = Inventory::with(['product.category', 'variant'])
            ->when(request('status') === 'in_stock', fn ($q) => $q->inStock())
            ->when(request('status') === 'low_stock', fn ($q) => $q->lowStock())
            ->when(request('status') === 'out_of_stock', fn ($q) => $q->outOfStock())
            ->when(request('search'), function ($q) {
                $search = request('search');
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventories.index', compact('inventories'));
    }

    public function create(): View
    {
        Gate::authorize('manage-inventory');

        $products = Product::active()->ordered()->get();

        return view('admin.inventories.create', compact('products'));
    }

    public function store(StoreInventoryRequest $request): RedirectResponse
    {
        Gate::authorize('manage-inventory');

        $product = Product::findOrFail($request->product_id);
        $variant = $request->product_variant_id
            ? $product->variants()->findOrFail($request->product_variant_id)
            : null;

        $this->inventoryService->stockIn(
            $product,
            $request->quantity,
            $request->reason,
            auth()->user(),
            $variant
        );

        return redirect()->route('admin.inventories.index')
            ->with('status', 'মজুদ সফলভাবে যোগ করা হয়েছে।');
    }

    public function edit(Inventory $inventory): View
    {
        Gate::authorize('manage-inventory');

        $inventory->load(['product', 'variant']);

        return view('admin.inventories.edit', compact('inventory'));
    }

    public function adjust(AdjustStockRequest $request, Inventory $inventory): RedirectResponse
    {
        Gate::authorize('manage-inventory');

        $this->inventoryService->adjustStock(
            $inventory->product,
            $request->quantity,
            $request->reason,
            auth()->user(),
            $inventory->variant
        );

        return redirect()->route('admin.inventories.index')
            ->with('status', 'মজুদ সফলভাবে সমন্বয় করা হয়েছে।');
    }

    public function destroy(Inventory $inventory): RedirectResponse
    {
        Gate::authorize('manage-inventory');

        $inventory->delete();

        return redirect()->route('admin.inventories.index')
            ->with('status', 'মজুদ রেকর্ড মুছে ফেলা হয়েছে।');
    }

    public function history(Inventory $inventory): View
    {
        Gate::authorize('manage-inventory');

        $inventory->load(['product', 'variant']);
        $adjustments = $inventory->adjustments()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.inventories.history', compact('inventory', 'adjustments'));
    }
}
