<x-admin.layout title="ড্যাশবোর্ড">
    <div class="admin-page-head">
        <div>
            <h1 class="h4 mb-1">ড্যাশবোর্ড</h1>
            <p class="text-muted mb-0 small">ব্যবসার এক নজরে সামগ্রিক চিত্র</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2 align-items-center">
            <select name="range" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="today" {{ $range === 'today' ? 'selected' : '' }}>আজ</option>
                <option value="yesterday" {{ $range === 'yesterday' ? 'selected' : '' }}>গতকাল</option>
                <option value="7_days" {{ $range === '7_days' ? 'selected' : '' }}>গত ৭ দিন</option>
                <option value="30_days" {{ $range === '30_days' ? 'selected' : '' }}>গত ৩০ দিন</option>
                <option value="this_month" {{ $range === 'this_month' ? 'selected' : '' }}>এই মাস</option>
                <option value="last_month" {{ $range === 'last_month' ? 'selected' : '' }}>গত মাস</option>
                <option value="this_year" {{ $range === 'this_year' ? 'selected' : '' }}>এই বছর</option>
            </select>
        </form>
    </div>

    {{-- KPI কার্ড --}}
    <div class="row g-3 mb-4">
        @php
            $kpiCards = [
                ['label' => 'আজকের বিক্রয়', 'value' => $kpis['today_sales'], 'icon' => 'bi-cash-stack', 'color' => 'success', 'sub' => 'আজ', 'money' => true],
                ['label' => 'আজকের অর্ডার', 'value' => $kpis['today_orders'], 'icon' => 'bi-receipt', 'color' => 'primary', 'sub' => 'আজ', 'money' => false],
                ['label' => 'মোট বিক্রয় (রেঞ্জ)', 'value' => $kpis['total_sales'], 'icon' => 'bi-graph-up-arrow', 'color' => 'success', 'sub' => 'নির্বাচিত রেঞ্জ', 'money' => true],
                ['label' => 'মোট অর্ডার (রেঞ্জ)', 'value' => $kpis['total_orders'], 'icon' => 'bi-collection', 'color' => 'primary', 'sub' => 'নির্বাচিত রেঞ্জ', 'money' => false],
                ['label' => 'মোট ক্রেতা', 'value' => $kpis['customers'], 'icon' => 'bi-people', 'color' => 'info', 'sub' => 'সব', 'money' => false],
                ['label' => 'মোট পণ্য', 'value' => $kpis['products'], 'icon' => 'bi-box-seam', 'color' => 'secondary', 'sub' => 'সব', 'money' => false],
                ['label' => 'অপেক্ষমাণ অর্ডার', 'value' => $kpis['pending_orders'], 'icon' => 'bi-hourglass-split', 'color' => 'warning', 'sub' => 'pending', 'money' => false],
                ['label' => 'কম স্টক', 'value' => $kpis['low_stock'], 'icon' => 'bi-exclamation-triangle', 'color' => 'danger', 'sub' => 'সতর্কতা', 'money' => false],
            ];
        @endphp
        @foreach ($kpiCards as $card)
            <div class="col-6 col-xl-3">
                <div class="admin-card admin-kpi p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="admin-kpi__label">{{ $card['label'] }}</div>
                            <div class="admin-kpi__value mt-1">
                                {{ $card['money'] ? \App\Support\BengaliNumber::money($card['value']) : \App\Support\BengaliNumber::format($card['value']) }}
                            </div>
                            <div class="admin-kpi__sub">{{ $card['sub'] }}</div>
                        </div>
                        <span class="admin-kpi__icon text-bg-{{ $card['color'] }}"><i class="bi {{ $card['icon'] }}"></i></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        {{-- বিক্রয় চার্ট --}}
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card__header">
                    <span>বিক্রয় প্রবণতা</span>
                    <span class="badge text-bg-light border">রাজস্ব</span>
                </div>
                <div class="admin-card__body">
                    <canvas id="salesChart" height="120" data-labels='@json($salesSeries['labels'])' data-values='@json($salesSeries['values'])'></canvas>
                    @if (empty(array_filter($salesSeries['values'])))
                        <div class="text-center text-muted small py-3">এই রেঞ্জে কোনো বিক্রয় ডেটা নেই।</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- অর্ডার অবস্থা চার্ট --}}
        <div class="col-lg-4">
            <div class="admin-card h-100">
                <div class="admin-card__header"><span>অর্ডারের অবস্থা</span></div>
                <div class="admin-card__body">
                    <canvas id="statusChart" height="120" data-counts='@json(array_column($statusCounts, 'count'))' data-labels='@json(array_column($statusCounts, 'label'))'></canvas>
                    <div class="mt-3">
                        @foreach ($statusCounts as $item)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span><span class="badge {{ $item['badge'] }} admin-badge">{{ $item['label'] }}</span></span>
                                <span class="fw-semibold">{{ \App\Support\BengaliNumber::format($item['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- সর্বাধিক বিক্রিত পণ্য --}}
        <div class="col-lg-7">
            <div class="admin-card h-100">
                <div class="admin-card__header"><span>সর্বাধিক বিক্রিত পণ্য</span></div>
                <div class="admin-card__body p-0">
                    @if (empty($topProducts))
                        <div class="text-center text-muted small py-5">এই রেঞ্জে কোনো বিক্রয় ডেটা নেই।</div>
                    @else
                        <div class="table-responsive">
                            <table class="table admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>পণ্য</th>
                                        <th class="text-center">বিক্রির পরিমাণ</th>
                                        <th class="text-center">অর্ডার</th>
                                        <th class="text-end">রাজস্ব</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topProducts as $row)
                                        <tr>
                                            <td class="fw-semibold">{{ $row['name'] }}</td>
                                            <td class="text-center">{{ \App\Support\BengaliNumber::format($row['qty']) }}</td>
                                            <td class="text-center">{{ \App\Support\BengaliNumber::format($row['orders']) }}</td>
                                            <td class="text-end fw-semibold">{{ \App\Support\BengaliNumber::money($row['revenue']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- কম স্টক সতর্কতা --}}
        <div class="col-lg-5">
            <div class="admin-card h-100">
                <div class="admin-card__header d-flex">
                    <span>কম স্টক সতর্কতা</span>
                    @can('inventory.view')
                        <a href="{{ route('admin.inventory.index') }}" class="small text-decoration-none">সব দেখুন →</a>
                    @endcan
                </div>
                <div class="admin-card__body p-0">
                    @if ($lowStock->isEmpty())
                        <div class="text-center text-muted small py-5">কোনো কম-স্টক পণ্য নেই।</div>
                    @else
                        <div class="table-responsive">
                            <table class="table admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>পণ্য / ভ্যারিয়েন্ট</th>
                                        <th class="text-center">বর্তমান স্টক</th>
                                        <th class="text-center">সতর্কতা সীমা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStock as $inv)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $inv->variant?->product?->name ?? '—' }}</div>
                                                <small class="text-muted">{{ $inv->variant?->name ?? '' }}</small>
                                            </td>
                                            <td class="text-center text-danger fw-semibold">{{ \App\Support\BengaliNumber::format($inv->available_quantity) }}</td>
                                            <td class="text-center">{{ \App\Support\BengaliNumber::format($inv->low_stock_threshold) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- সাম্প্রতিক অর্ডার --}}
    <div class="admin-card">
        <div class="admin-card__header d-flex">
            <span>সাম্প্রতিক অর্ডার</span>
            @can('orders.view')
                @if (\Illuminate\Support\Facades\Route::has('admin.orders.index'))
                    <a href="{{ route('admin.orders.index') }}" class="small text-decoration-none">সব অর্ডার →</a>
                @endif
            @endcan
        </div>
        <div class="admin-card__body p-0">
            @if ($recentOrders->isEmpty())
                <div class="text-center text-muted small py-5">এই রেঞ্জে কোনো অর্ডার নেই।</div>
            @else
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>অর্ডার নম্বর</th>
                                <th>ক্রেতা</th>
                                <th>মোট</th>
                                <th>অবস্থা</th>
                                <th>পেমেন্ট</th>
                                <th>তারিখ</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-semibold">#{{ $order->order_number }}</a></td>
                                    <td>{{ $order->user?->name ?? $order->receiver_name }}</td>
                                    <td class="fw-semibold">{{ \App\Support\BengaliNumber::money($order->grand_total) }}</td>
                                    <td>
                                        @php
                                            $os = \App\Enums\OrderStatus::tryFrom($order->status);
                                        @endphp
                                        <span class="badge {{ $os?->badgeClass() ?? 'text-bg-secondary' }} admin-badge">{{ $os?->label() ?? $order->status }}</span>
                                    </td>
                                    <td><small class="text-muted">{{ $order->payment_status }}</small></td>
                                    <td><small class="text-muted">{{ $order->created_at->format('d M, Y h:i A') }}</small></td>
                                    <td class="text-end row-actions">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const salesEl = document.getElementById('salesChart');
            if (salesEl && window.Chart) {
                let labels = [];
                let values = [];
                try { labels = JSON.parse(salesEl.dataset.labels); } catch (e) {}
                try { values = JSON.parse(salesEl.dataset.values); } catch (e) {}
                new Chart(salesEl, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'রাজস্ব (৳)',
                            data: values,
                            borderColor: '#1b7f3e',
                            backgroundColor: 'rgba(27,127,62,0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { maxTicksLimit: 6 } },
                            x: { ticks: { maxTicksLimit: 12, maxRotation: 0 } }
                        }
                    }
                });
            }

            const statusEl = document.getElementById('statusChart');
            if (statusEl && window.Chart) {
                let labels = [];
                let counts = [];
                try { labels = JSON.parse(statusEl.dataset.labels); } catch (e) {}
                try { counts = JSON.parse(statusEl.dataset.counts); } catch (e) {}
                new Chart(statusEl, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: ['#ffc107','#0d6efd','#0dcaf0','#6c757d','#198754','#dc3545','#212529'],
                            borderWidth: 1,
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        });
    </script>
@endpush
