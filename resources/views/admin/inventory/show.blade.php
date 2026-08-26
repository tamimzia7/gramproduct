<x-admin.layout title="{{ __('inventory.title') }} — {{ $inventory->variant?->name }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $inventory->variant?->product?->name }}</h1>
            <p class="text-muted mb-0">{{ $inventory->variant?->name }} — <code>{{ $inventory->variant?->sku }}</code></p>
        </div>
        <div class="d-flex gap-2">
            @can('addStock', $inventory)
                <a href="{{ route('admin.inventory.add-form', $inventory) }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i>{{ __('inventory.actions.add_stock') }}
                </a>
            @endcan
            @can('adjust', $inventory)
                <a href="{{ route('admin.inventory.adjust-form', $inventory) }}" class="btn btn-outline-warning">
                    <i class="bi bi-sliders me-1"></i>{{ __('inventory.actions.adjust_stock') }}
                </a>
            @endcan
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>ফিরে যান
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            {{-- স্টক তথ্য --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">স্টক তথ্য</h2></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width: 45%;">{{ __('inventory.fields.quantity') }}</th>
                            <td class="fw-bold fs-5">{{ \App\Support\BengaliNumber::format($inventory->quantity) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('inventory.fields.reserved_quantity') }}</th>
                            <td>{{ \App\Support\BengaliNumber::format($inventory->reserved_quantity) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('inventory.fields.available_quantity') }}</th>
                            <td class="fw-bold text-success">{{ \App\Support\BengaliNumber::format($inventory->available_quantity) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('inventory.fields.low_stock_threshold') }}</th>
                            <td>{{ \App\Support\BengaliNumber::format($inventory->low_stock_threshold) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('inventory.fields.allow_backorder') }}</th>
                            <td>
                                @if ($inventory->allow_backorder)
                                    <span class="badge text-bg-info">হ্যাঁ</span>
                                @else
                                    <span class="badge text-bg-light border">না</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('inventory.fields.status') }}</th>
                            <td>
                                @php
                                    $available = $inventory->available_quantity;
                                @endphp
                                @if ($available <= 0 && ! $inventory->allow_backorder)
                                    <span class="badge text-bg-danger">{{ __('inventory.statuses.out_of_stock') }}</span>
                                @elseif ($available <= 0)
                                    <span class="badge text-bg-info">{{ __('inventory.statuses.backorder') }}</span>
                                @elseif ($available <= $inventory->low_stock_threshold)
                                    <span class="badge text-bg-warning text-dark">{{ __('inventory.statuses.low_stock') }}</span>
                                @else
                                    <span class="badge text-bg-success">{{ __('inventory.statuses.in_stock') }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- ভ্যারিয়েন্ট তথ্য --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h6 mb-0">ভ্যারিয়েন্ট</h2></div>
                <div class="card-body">
                    <p class="mb-1"><strong>পণ্য:</strong> {{ $inventory->variant?->product?->name ?? '—' }}</p>
                    <p class="mb-1"><strong>ভ্যারিয়েন্ট:</strong> {{ $inventory->variant?->name ?? '—' }}</p>
                    <p class="mb-0"><strong>SKU:</strong> <code>{{ $inventory->variant?->sku ?? '—' }}</code></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- লেনদেন ইতিহাস --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h6 mb-0">{{ __('inventory.history.title') }}</h2></div>
                <div class="card-body">
                    @if ($transactions->isEmpty())
                        <p class="text-muted mb-0">{{ __('inventory.history.empty') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="small text-muted">
                                        <th>{{ __('inventory.fields.date') }}</th>
                                        <th>{{ __('inventory.fields.type') }}</th>
                                        <th class="text-end">{{ __('inventory.fields.amount') }}</th>
                                        <th class="text-center">{{ __('inventory.fields.stock_before') }} → {{ __('inventory.fields.stock_after') }}</th>
                                        <th>{{ __('inventory.fields.reason_note') }}</th>
                                        <th>{{ __('inventory.fields.user') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td class="small text-muted">{{ $transaction->created_at->format('d M, Y H:i') }}</td>
                                            <td><span class="badge text-bg-secondary-subtle text-secondary-emphasis border">{{ $transaction->type->label() }}</span></td>
                                            <td class="text-end fw-semibold {{ $transaction->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ ($transaction->quantity >= 0 ? '+' : '').\App\Support\BengaliNumber::format($transaction->quantity) }}
                                            </td>
                                            <td class="text-center small text-muted">
                                                {{ \App\Support\BengaliNumber::format($transaction->stock_before) }} →
                                                <span class="fw-semibold text-dark">{{ \App\Support\BengaliNumber::format($transaction->stock_after) }}</span>
                                            </td>
                                            <td class="small">{{ $transaction->note ?? '—' }}</td>
                                            <td class="small">{{ $transaction->user?->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
