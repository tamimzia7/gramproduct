<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddStockRequest;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    /**
     * ইনভেন্টরি ড্যাশবোর্ড + পূর্ণ তালিকা
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inventory::class);

        // ---------- ড্যাশবোর্ড স্ট্যাটস ----------
        $availableExpr = '(inventories.quantity - inventories.reserved_quantity)';

        $stats = [
            'total_variants' => Inventory::count(),
            'in_stock' => Inventory::whereRaw("{$availableExpr} > low_stock_threshold")->count(),
            'low_stock' => Inventory::whereRaw("{$availableExpr} > 0")
                ->whereRaw("{$availableExpr} <= low_stock_threshold")->count(),
            'out_of_stock' => Inventory::whereRaw("{$availableExpr} <= 0")
                ->where('allow_backorder', false)->count(),
            'total_quantity' => (int) Inventory::sum('quantity'),
            'total_reserved' => (int) Inventory::sum('reserved_quantity'),
        ];

        // ---------- সাম্প্রতিক কার্যক্রম ----------
        $recentActivity = InventoryTransaction::query()
            ->with(['variant.product', 'user'])
            ->latestFirst()
            ->limit(8)
            ->get();

        // ---------- ইনভেন্টরি তালিকা ----------
        $query = Inventory::query()
            ->with(['variant.product']);

        if ($search = trim((string) $request->input('q'))) {
            $query->whereHas('variant', function ($variantQuery) use ($search): void {
                $variantQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
            });
        }

        match ($request->input('status')) {
            'in_stock' => $query->whereRaw("{$availableExpr} > low_stock_threshold"),
            'low_stock' => $query->whereRaw("{$availableExpr} > 0")->whereRaw("{$availableExpr} <= low_stock_threshold"),
            'out_of_stock' => $query->whereRaw("{$availableExpr} <= 0")->where('allow_backorder', false),
            default => null,
        };

        $inventories = $query
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inventory.index', compact('stats', 'recentActivity', 'inventories'));
    }

    /**
     * ইনভেন্টরি বিস্তারিত + লেনদেন ইতিহাস
     */
    public function show(Inventory $inventory): View
    {
        $this->authorize('view', $inventory);

        $inventory->load(['variant.product']);

        $transactions = InventoryTransaction::query()
            ->forVariant($inventory->product_variant_id)
            ->with('user')
            ->latestFirst()
            ->paginate(15);

        return view('admin.inventory.show', compact('inventory', 'transactions'));
    }

    /**
     * স্টক যোগ করার ফর্ম
     */
    public function addForm(Inventory $inventory): View
    {
        $this->authorize('addStock', $inventory);

        $inventory->load('variant.product');

        return view('admin.inventory.add', compact('inventory'));
    }

    /**
     * স্টক যোগ সংরক্ষণ (type=restock)
     */
    public function add(AddStockRequest $request, Inventory $inventory): RedirectResponse
    {
        $this->authorize('addStock', $inventory);

        try {
            $this->inventoryService->addStock(
                $inventory->variant,
                (int) $request->input('quantity'),
                $request->input('note'),
                $request->user(),
            );
        } catch (InventoryException $exception) {
            return back()->withInput()->withErrors(['quantity' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.inventory.show', $inventory)
            ->with('success', __('inventory.messages.stock_added'));
    }

    /**
     * স্টক সমন্বয়ের ফর্ম
     */
    public function adjustForm(Inventory $inventory): View
    {
        $this->authorize('adjust', $inventory);

        $inventory->load('variant.product');

        return view('admin.inventory.adjust', compact('inventory'));
    }

    /**
     * ম্যানুয়াল সমন্বয় সংরক্ষণ (type=adjustment)
     */
    public function adjust(AdjustStockRequest $request, Inventory $inventory): RedirectResponse
    {
        $this->authorize('adjust', $inventory);

        try {
            $this->inventoryService->adjustStock(
                $inventory->variant,
                (int) $request->integer('quantity'),
                $request->input('reason'),
                $request->user(),
            );
        } catch (InventoryException $exception) {
            return back()->withInput()->withErrors(['quantity' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.inventory.show', $inventory)
            ->with('success', __('inventory.messages.stock_adjusted'));
    }
}
