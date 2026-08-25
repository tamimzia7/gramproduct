<x-layouts.app title="মজুদ ইতিহাস">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">মজুদ ইতিহাস</h1>
                <p class="text-muted mb-0">{{ $inventory->product->name }} — {{ $inventory->variant?->name ?? 'মূল পণ্য' }}</p>
            </div>
            <a href="{{ route('admin.inventories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> ফিরে যান
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>তারিখ</th>
                                <th>ধরন</th>
                                <th>পরিমাণ</th>
                                <th>আগের মজুদ</th>
                                <th>নতুন মজুদ</th>
                                <th>কারণ</th>
                                <th>ব্যবহারকারী</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adjustments as $adjustment)
                                <tr>
                                    <td>{{ $adjustment->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @switch($adjustment->type)
                                            @case('stock_in')
                                                <span class="badge text-bg-success">স্টক ইন</span>
                                                @break
                                            @case('stock_out')
                                                <span class="badge text-bg-danger">স্টক আউট</span>
                                                @break
                                            @case('adjustment')
                                                <span class="badge text-bg-info">সমন্বয়</span>
                                                @break
                                            @case('wastage')
                                                <span class="badge text-bg-warning text-dark">নষ্ট পণ্য</span>
                                                @break
                                            @case('damage')
                                                <span class="badge text-bg-secondary">ক্ষতিগ্রস্ত</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if ($adjustment->quantity > 0)
                                            <span class="text-success fw-semibold">+{{ $adjustment->quantity }}</span>
                                        @else
                                            <span class="text-danger fw-semibold">{{ $adjustment->quantity }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $adjustment->previous_quantity }}</td>
                                    <td>{{ $adjustment->new_quantity }}</td>
                                    <td>{{ $adjustment->reason ?? '—' }}</td>
                                    <td>{{ $adjustment->user?->name ?? 'সিস্টেম' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">কোনো ইতিহাস পাওয়া যায়নি।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $adjustments->links() }}
        </div>
    </div>
</x-layouts.app>
