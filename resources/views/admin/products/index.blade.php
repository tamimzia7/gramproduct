<x-layouts.app title="পণ্য ব্যবস্থাপনা">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">পণ্য ব্যবস্থাপনা</h1>
                <p class="text-muted mb-0">পণ্য তৈরি, সম্পাদনা এবং পরিচালনা করুন।</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> নতুন পণ্য যোগ করুন
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
                <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">অনুসন্ধান</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="পণ্যের নাম বা SKU..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="category_id" class="form-label">বিভাগ</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">সব বিভাগ</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                                    {{ str_repeat('— ', $cat->depth ?? 0) }}{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">স্ট্যাটাস</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">সব</option>
                            <option value="active" @selected(request('status') === 'active')">সক্রিয়</option>
                            <option value="inactive" @selected(request('status') === 'inactive')">নিষ্ক্রিয়</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="flag" class="form-label">বৈশিষ্ট্য</label>
                        <select id="flag" name="flag" class="form-select">
                            <option value="">সব</option>
                            <option value="featured" @selected(request('flag') === 'featured')">বৈশিষ্ট্যযুক্ত</option>
                            <option value="bestseller" @selected(request('flag') === 'bestseller')">বেস্টসেলার</option>
                            <option value="new_arrival" @selected(request('flag') === 'new_arrival')">নতুন আগমন</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="bi bi-search"></i> খুঁজুন</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">রিসেট</a>
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
                                <th style="width:50px">ছবি</th>
                                <th>নাম</th>
                                <th>SKU</th>
                                <th>বিভাগ</th>
                                <th>ভ্যারিয়েন্ট</th>
                                <th>মূল্য</th>
                                <th>স্ট্যাটাস</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        @if ($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                                 class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                        @else
                                            <div class="bg-success-subtle d-flex align-items-center justify-content-center rounded"
                                                 style="width:40px;height:40px;">
                                                <i class="bi bi-image text-success"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-decoration-none fw-semibold">
                                            {{ $product->name }}
                                        </a>
                                        @if ($product->is_featured)
                                            <i class="bi bi-star-fill text-warning ms-1 small" title="বৈশিষ্ট্যযুক্ত"></i>
                                        @endif
                                        @if ($product->is_bestseller)
                                            <i class="bi bi-fire text-danger ms-1 small" title="বেস্টসেলার"></i>
                                        @endif
                                        @if ($product->is_new_arrival)
                                            <i class="bi bi-clock-history text-info ms-1 small" title="নতুন আগমন"></i>
                                        @endif
                                    </td>
                                    <td><code>{{ $product->sku }}</code></td>
                                    <td>{{ $product->category->name ?? '—' }}</td>
                                    <td>
                                        @if ($product->variants_count > 0)
                                            <a href="{{ route('admin.products.variants.index', $product) }}" class="text-decoration-none">
                                                {{ $product->variants_count }}টি
                                            </a>
                                        @else
                                            <span class="text-muted">০</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($product->hasDiscount())
                                            <span class="text-decoration-line-through text-muted small">৳{{ number_format($product->base_price, 2) }}</span>
                                            <span class="text-danger fw-semibold">৳{{ number_format($product->discount_price, 2) }}</span>
                                        @else
                                            <span class="fw-semibold">৳{{ number_format($product->base_price, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($product->is_active)
                                            <span class="badge text-bg-success">সক্রিয়</span>
                                        @else
                                            <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> সম্পাদনা
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি এই পণ্যটি মুছতে চান?');">
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
                                    <td colspan="8" class="text-center text-muted py-4">কোনো পণ্য পাওয়া যায়নি।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.app>
