<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * অ্যাডমিন ড্যাশবোর্ড — বাস্তব DB ডেটা থেকে KPI ও চার্ট।
 * কোনো fake সংখ্যা নয়; সব অ্যাগ্রিগেশন database-স্তরে হয়।
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->input('range', 'today');

        [$start, $end] = $this->rangeDates($range, $request);

        return view('admin.dashboard', [
            'kpis' => $this->kpis($start, $end),
            'salesSeries' => $this->salesSeries($start, $end),
            'statusCounts' => $this->statusCounts($end),
            'topProducts' => $this->topProducts($start, $end),
            'recentOrders' => $this->recentOrders(),
            'lowStock' => $this->lowStock(),
            'range' => $range,
            'stats' => [
                'products' => Product::withTrashed()->count(),
                'categories' => Category::withTrashed()->count(),
                'customers' => User::whereDoesntHave('roles')->count(),
                'farmers' => User::whereHas('roles', fn ($q) => $q->where('slug', 'farmer'))->count(),
                'total_stock' => Inventory::sum('quantity'),
            ],
        ]);
    }

    /**
     * KPI গণনা — dashboard-এর শীর্ষ কার্ড।
     *
     * @return array<string, mixed>
     */
    private function kpis(Carbon $start, Carbon $end): array
    {
        $today = Carbon::today();
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        $todaySales = $this->revenueBetween($todayStart, $todayEnd);
        $todayOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        return [
            'today_sales' => $todaySales,
            'today_orders' => $todayOrders,
            'total_sales' => $this->revenueBetween($start, $end),
            'total_orders' => Order::whereBetween('created_at', [$start, $end])->count(),
            'customers' => User::whereDoesntHave('roles')->count(),
            'products' => Product::withTrashed()->count(),
            'farmers' => User::whereHas('roles', fn ($q) => $q->where('slug', 'farmer'))->count(),
            'total_stock' => Inventory::sum('quantity'),
            'low_stock' => Inventory::query()
                ->whereColumn('quantity', '>', 'reserved_quantity')
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
        ];
    }

    private function revenueBetween(Carbon $start, Carbon $end): float
    {
        return (float) Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', OrderStatus::CANCELLED->value)
            ->sum('grand_total');
    }

    /**
     * বিক্রয় লাইনের ডেটা (json-এর জন্য)।
     *
     * @return array<string, mixed>
     */
    private function salesSeries(Carbon $start, Carbon $end): array
    {
        $grouped = Order::where('status', '!=', OrderStatus::CANCELLED->value)
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as d, SUM(grand_total) as revenue, COUNT(*) as count')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('revenue', 'd');

        $labels = [];
        $values = [];
        $counts = [];

        $cursor = $start->copy()->startOfDay();
        $limit = $cursor->diffInDays($end->copy()->endOfDay()) + 1;

        if ($limit > 366) {
            $cursor = $start->copy()->startOfMonth();
        }

        $i = 0;
        while (($limit > 366 ? $cursor->lte($end) : $i < $limit)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $limit > 366 ? $cursor->format('M y') : $cursor->format('d M');
            $values[] = (float) ($grouped[$key] ?? 0);
            $counts[] = (int) (Order::whereBetween('created_at', [$cursor->copy()->startOfDay(), $cursor->copy()->endOfDay()])
                ->where('status', '!=', OrderStatus::CANCELLED->value)->count());
            $cursor->addDay();
            $i++;
            if ($i > 400) {
                break;
            }
        }

        return ['labels' => $labels, 'values' => $values, 'counts' => $counts];
    }

    /**
     * অর্ডারের অবস্থা অনুযায়ী বণ্টন (চার্ট)।
     *
     * @return array<int, array<string, mixed>>
     */
    private function statusCounts(Carbon $through): array
    {
        $counts = Order::where('created_at', '<=', $through->copy()->endOfDay())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = [];
        foreach (OrderStatus::values() as $value) {
            $status = OrderStatus::from($value);
            $result[] = [
                'status' => $value,
                'label' => $status->label(),
                'badge' => $status->badgeClass(),
                'count' => (int) ($counts[$value] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * সর্বাধিক বিক্রিত পণ্য — DB অ্যাগ্রিগেশন।
     *
     * @return array<int, array<string, mixed>>
     */
    private function topProducts(Carbon $start, Carbon $end): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', OrderStatus::CANCELLED->value)
            ->whereBetween('orders.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as qty, COUNT(DISTINCT order_items.order_id) as orders, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->product_name,
                'qty' => (int) $row->qty,
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    /**
     * সাম্প্রতিক অর্ডার।
     *
     * @return Collection
     */
    private function recentOrders()
    {
        return Order::with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * সতর্কতা-সীমার নিচে থাকা স্টক।
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function lowStock()
    {
        return Inventory::with('variant.product')
            ->whereColumn('quantity', '>', 'reserved_quantity')
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->orderByRaw('quantity - reserved_quantity asc')
            ->limit(8)
            ->get();
    }

    /**
     * তারিখ রেঞ্জ নির্বাচন।
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rangeDates(string $range, Request $request): array
    {
        $now = Carbon::now();

        return match ($range) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            '7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfDay()],
            'custom' => [
                $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : $now->copy()->startOfMonth(),
                $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : $now->copy()->endOfDay(),
            ],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
