<x-layouts.app title="মজুদ ব্যবস্থাপনা">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">মজুদ ব্যবস্থাপনা</h1>
                <p class="text-muted mb-0">পণ্যের মজুদ পরিচালনা করুন।</p>
            </div>
            <a href="{{ route('admin.inventories.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> মজুদ যোগ করুন
            </a>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.inventories.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">অনুসন্ধান</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="পণ্যের নাম বা SKU..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">স্ট্যাটাস</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">সব</option>
                            <option value="in_stock" @selected(request('status') === 'in_stock')">মজুদ আছে</option>
                            <option value="low_stock" @selected(request('status') === 'low_stock')">কম মজুদ</option>
                            <option value="out_of_stock" @selected(request('status') === 'out_of_stock')">মজুদ শেষ</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="bi bi-search"></i> খুঁজুন</button>
                        <a href="{{ route('admin.inventories.index') }}" class="btn btn-outline-secondary">রিসেট</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>পণ্য</th>
                                <th>ভ্যারিয়েন্ট</th>
                                <th>মজুদ</th>
                                <th>উপলব্ধ</th>
                                <th>সংরক্ষিত</th>
                                <th>কম মজুদ</th>
                                <th>স্ট্যাটাস</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventories as $inventory)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.products.edit', $inventory->product) }}" class="text-decoration-none fw-semibold">
                                            {{ $inventory->product->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $inventory->product->sku }}</small>
                                    </td>
                                    <td>{{ $inventory->variant?->name ?? '—' }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $inventory->quantity }}</span>
                                        <small class="text-muted">{{ $inventory->product->unit }}</small>
                                    </td>
                                    <td>{{ $inventory->available_quantity }}</td>
                                    <td>{{ $inventory->reserved_quantity }}</td>
                                    <td>{{ $inventory->low_stock_threshold }}</td>
                                    <td>
                                        @if ($inventory->isOutOfStock())
                                            <span class="badge text-bg-danger">মজুদ শেষ</span>
                                        @elseif ($inventory->isLowStock())
                                            <span class="badge text-bg-warning text-dark">কম মজুদ</span>
                                        @else
                                            <span class="badge text-bg-success">মজুদ আছে</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.inventories.history', $inventory) }}" class="btn btn-sm btn-outline-info" title="ইতিহাস">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                        <a href="{{ route('admin.inventories.edit', $inventory) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> সমন্বয়
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">কোনো মজুদ রেকর্ড পাওয়া যায়নি।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $inventories->links() }}
        </div>
    </div>
</x-layouts.app>
