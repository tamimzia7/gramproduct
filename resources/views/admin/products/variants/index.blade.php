<x-layouts.app title="ভ্যারিয়েন্ট ব্যবস্থাপনা">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">ভ্যারিয়েন্ট ব্যবস্থাপনা</h1>
                <p class="text-muted mb-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-success text-decoration-none">{{ $product->name }}</a> — পণ্যের ভ্যারিয়েন্ট তৈরি, সম্পাদনা এবং পরিচালনা করুন।
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> পণ্যে ফিরে যান
                </a>
                <a href="{{ route('admin.products.variants.create', $product) }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> নতুন ভ্যারিয়েন্ট যোগ করুন
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>নাম</th>
                                <th>SKU</th>
                                <th>ওজন/পরিমাণ</th>
                                <th>মূল্য</th>
                                <th>সর্বনিম্ন অর্ডার</th>
                                <th>সর্বোচ্চ অর্ডার</th>
                                <th>স্ট্যাটাস</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($variants as $variant)
                                <tr>
                                    <td class="fw-semibold">{{ $variant->name }}</td>
                                    <td><code>{{ $variant->sku }}</code></td>
                                    <td>
                                        @if ($variant->weight)
                                            {{ number_format($variant->weight, 2) }} {{ $variant->unit }}
                                        @else
                                            {{ $variant->unit }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($variant->hasDiscount())
                                            <span class="text-decoration-line-through text-muted small">৳{{ number_format($variant->price, 2) }}</span>
                                            <span class="text-danger fw-semibold">৳{{ number_format($variant->discount_price, 2) }}</span>
                                        @else
                                            <span class="fw-semibold">৳{{ number_format($variant->price, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $variant->minimum_order }}</td>
                                    <td>{{ $variant->maximum_order ?? '—' }}</td>
                                    <td>
                                        @if ($variant->is_active)
                                            <span class="badge text-bg-success">সক্রিয়</span>
                                        @else
                                            <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> সম্পাদনা
                                        </a>
                                        <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি এই ভ্যারিয়েন্টটি মুছতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> মুছুন
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        কোনো ভ্যারিয়েন্ট পাওয়া যায়নি।
                                        <a href="{{ route('admin.products.variants.create', $product) }}" class="d-block mt-2 text-success">ভ্যারিয়েন্ট যোগ করুন</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
