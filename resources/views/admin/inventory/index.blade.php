<x-admin.layout title="{{ __('inventory.dashboard') }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('inventory.dashboard') }}</h1>
            <p class="text-muted mb-0">ভ্যারিয়েন্ট-ভিত্তিক স্টক ব্যবস্থাপনা</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
        </div>
    @endif

    {{-- ড্যাশবোর্ড স্ট্যাটস --}}
    <div class="row g-3 mb-4">
        @php
            $statCards = [
                ['label' => __('inventory.stats.total_variants'), 'value' => \App\Support\BengaliNumber::format($stats['total_variants']), 'icon' => 'bi-boxes', 'class' => 'text-bg-light text-dark'],
                ['label' => __('inventory.stats.in_stock'), 'value' => \App\Support\BengaliNumber::format($stats['in_stock']), 'icon' => 'bi-check-circle', 'class' => 'text-bg-success'],
                ['label' => __('inventory.stats.low_stock'), 'value' => \App\Support\BengaliNumber::format($stats['low_stock']), 'icon' => 'bi-exclamation-triangle', 'class' => 'text-bg-warning text-dark'],
                ['label' => __('inventory.stats.out_of_stock'), 'value' => \App\Support\BengaliNumber::format($stats['out_of_stock']), 'icon' => 'bi-x-circle', 'class' => 'text-bg-danger'],
                ['label' => __('inventory.stats.total_quantity'), 'value' => \App\Support\BengaliNumber::format($stats['total_quantity']), 'icon' => 'bi-stack', 'class' => 'text-bg-primary'],
                ['label' => __('inventory.stats.total_reserved'), 'value' => \App\Support\BengaliNumber::format($stats['total_reserved']), 'icon' => 'bi-bookmark-check', 'class' => 'text-bg-secondary'],
            ];
        @endphp
        @foreach ($statCards as $card)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card {{ $card['class'] }} border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75">{{ $card['label'] }}</div>
                                <div class="fs-4 fw-bold">{{ $card['value'] }}</div>
                            </div>
                            <i class="bi {{ $card['icon'] }} fs-3 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        {{-- সাম্প্রতিক কার্যক্রম --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h2 class="h6 mb-0">{{ __('inventory.history.recent_activity') }}</h2></div>
                <div class="card-body p-0">
                    @if ($recentActivity->isEmpty())
                        <p class="text-muted small p-3 mb-0">{{ __('inventory.history.empty') }}</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentActivity as $transaction)
                                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                    <div class="small">
                                        <span class="fw-semibold">{{ $transaction->variant?->product?->name }}</span>
                                        <span class="text-muted">({{ $transaction->variant?->name }})</span><br>
                                        <span class="badge {{ $transaction->quantity > 0 ? 'text-bg-success-subtle text-success border border-success-subtle' : ($transaction->type->value === 'reservation' ? 'text-bg-info-subtle text-info-emphasis border border-info-subtle' : 'text-bg-secondary-subtle text-secondary-emphasis border') }}">
                                            {{ $transaction->type->label() }}
                                        </span>
                                    </div>
                                    <div class="text-end small text-muted">
                                        <span class="fw-semibold {{ $transaction->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ ($transaction->quantity >= 0 ? '+' : '').\App\Support\BengaliNumber::format($transaction->quantity) }}
                                        </span><br>
                                        {{ $transaction->created_at->format('d M, H:i') }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- ফিল্টার --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h2 class="h6 mb-0">অনুসন্ধান ও ফিল্টার</h2></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.inventory.index') }}">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label for="q" class="form-label visually-hidden">অনুসন্ধান</label>
                                <input type="text" id="q" name="q" value="{{ request('q') }}"
                                       class="form-control" placeholder="{{ __('inventory.filters.search_placeholder') }}">
                            </div>
                            <div class="col-md-4">
                                <select id="status" name="status" class="form-select"
                                        onchange="this.form.submit()"
                                        aria-label="{{ __('inventory.fields.status') }}">
                                    <option value="">{{ __('inventory.filters.all') }}</option>
                                    <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>{{ __('inventory.statuses.in_stock') }}</option>
                                    <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>{{ __('inventory.statuses.low_stock') }}</option>
                                    <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>{{ __('inventory.statuses.out_of_stock') }}</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-success btn-sm mt-2">
                            <i class="bi bi-funnel me-1"></i>{{ __('inventory.filters.filter') }}
                        </button>
                    </form>
                    <p class="text-muted small mt-3 mb-0">
                        উপলব্ধ স্টক = বর্তমান স্টক − সংরক্ষিত স্টক। সীমার মধ্যে থাকলে "স্টক কম" দেখানো হয়।
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ইনভেন্টরি তালিকা --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">স্টক তালিকা ({{ \App\Support\BengaliNumber::format($inventories->total()) }})</h2>
        </div>
        <div class="card-body">
            @if ($inventories->isEmpty())
                <p class="text-muted mb-0">{{ __('inventory.messages.no_inventory') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>{{ __('inventory.fields.product') }}</th>
                                <th>{{ __('inventory.fields.variant') }}</th>
                                <th>{{ __('inventory.fields.sku') }}</th>
                                <th class="text-end">{{ __('inventory.fields.quantity') }}</th>
                                <th class="text-end">{{ __('inventory.fields.reserved_quantity') }}</th>
                                <th class="text-end">{{ __('inventory.fields.available_quantity') }}</th>
                                <th class="text-end">{{ __('inventory.fields.low_stock_threshold') }}</th>
                                <th>{{ __('inventory.fields.status') }}</th>
                                <th>{{ __('inventory.fields.last_updated') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventories as $item)
                                @php
                                    $available = $item->available_quantity;
                                    if ($available <= 0 && ! $item->allow_backorder) {
                                        $statusBadge = ['text-bg-danger', __('inventory.statuses.out_of_stock')];
                                    } elseif ($available <= 0) {
                                        $statusBadge = ['text-bg-info', __('inventory.statuses.backorder')];
                                    } elseif ($available <= $item->low_stock_threshold) {
                                        $statusBadge = ['text-bg-warning text-dark', __('inventory.statuses.low_stock').' — '.__('inventory.statuses.remaining', ['count' => \App\Support\BengaliNumber::format($available)])];
                                    } else {
                                        $statusBadge = ['text-bg-success', __('inventory.statuses.in_stock')];
                                    }
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $item->variant?->product?->name ?? '—' }}</td>
                                    <td>{{ $item->variant?->name ?? '—' }}</td>
                                    <td><code>{{ $item->variant?->sku ?? '—' }}</code></td>
                                    <td class="text-end">{{ \App\Support\BengaliNumber::format($item->quantity) }}</td>
                                    <td class="text-end text-muted">{{ \App\Support\BengaliNumber::format($item->reserved_quantity) }}</td>
                                    <td class="text-end fw-semibold">{{ \App\Support\BengaliNumber::format($available) }}</td>
                                    <td class="text-end text-muted">{{ \App\Support\BengaliNumber::format($item->low_stock_threshold) }}</td>
                                    <td>
                                        <span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $item->updated_at->format('d M, Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1 justify-content-end">
                                            @can('view', $item)
                                                <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-outline-secondary btn-sm"
                                                   title="{{ __('inventory.actions.view_details') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            @can('addStock', $item)
                                                <a href="{{ route('admin.inventory.add-form', $item) }}" class="btn btn-outline-success btn-sm">
                                                    {{ __('inventory.actions.add_stock') }}
                                                </a>
                                            @endcan
                                            @can('adjust', $item)
                                                <a href="{{ route('admin.inventory.adjust-form', $item) }}" class="btn btn-outline-warning btn-sm">
                                                    {{ __('inventory.actions.adjust_stock') }}
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $inventories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
