<x-admin.layout title="পণ্যসমূহ">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">পণ্যসমূহ</h1>
            <p class="text-muted mb-0">সব পণ্য পরিচালনা করুন</p>
        </div>
        @can('create', App\Models\Product::class)
            <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>নতুন পণ্য যোগ করুন
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ফিল্টার --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label small text-muted">খুঁজুন (নাম / SKU)</label>
                        <input type="text" id="search" name="search" class="form-control"
                               placeholder="পণ্যের নাম বা SKU..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="category_id" class="form-label small text-muted">ক্যাটাগরি</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">সব ক্যাটাগরি</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label small text-muted">স্ট্যাটাস</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">সব</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="featured" class="form-label small text-muted">বিশেষ পণ্য</label>
                        <select id="featured" name="featured" class="form-select">
                            <option value="">সব</option>
                            <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>হ্যাঁ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="bestseller" class="form-label small text-muted">সর্বাধিক বিক্রিত</label>
                        <select id="bestseller" name="bestseller" class="form-select">
                            <option value="">সব</option>
                            <option value="1" {{ request('bestseller') === '1' ? 'selected' : '' }}>হ্যাঁ</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-outline-success" title="ফিল্টার করুন">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- পণ্য টেবিল --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($products->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-box-seam fs-1 text-muted"></i>
                    <p class="mt-3 text-muted mb-0">কোনো পণ্য পাওয়া যায়নি।</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ছবি</th>
                                <th>পণ্যের নাম</th>
                                <th>SKU</th>
                                <th>ক্যাটাগরি</th>
                                <th>মূল্য</th>
                                <th class="text-center">স্ট্যাটাস</th>
                                <th class="text-center">বিশেষ পণ্য</th>
                                <th class="text-center">সর্বাধিক বিক্রিত</th>
                                <th class="text-center">স্টক</th>
                                <th>তারিখ</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        @php
                                            $thumb = $product->imageUrl();
                                        @endphp
                                        @if ($thumb)
                                            <img src="{{ $thumb }}" alt="{{ $product->imageAltText() }}"
                                                 class="rounded" style="width:44px;height:44px;object-fit:cover;">
                                        @else
                                            <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center"
                                                 style="width:44px;height:44px;">
                                                <i class="bi bi-image text-success small"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.products.show', $product) }}" class="text-decoration-none fw-semibold">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td><code>{{ $product->sku ?? '—' }}</code></td>
                                    <td>{{ $product->category?->name }}</td>
                                    <td class="fw-semibold">@price($product->effectivePrice(), $product->unitLabel())</td>
                                    <td class="text-center">
                                        @if ($product->is_active)
                                            <span class="badge text-bg-success">সক্রিয়</span>
                                        @else
                                            <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($product->is_featured)
                                            <i class="bi bi-star-fill text-warning" title="বিশেষ পণ্য"></i>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($product->is_bestseller)
                                            <i class="bi bi-fire text-danger" title="সর্বাধিক বিক্রিত"></i>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($product->isInStock())
                                            <span class="badge text-bg-success">{{ __('product.stock.in_stock') }}</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ __('product.stock.out_of_stock') }}</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $product->created_at->format('d M, Y') }}</small></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-secondary" title="দেখুন">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('update', $product)
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary" title="সম্পাদনা">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $product)
                                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন"
                                                            onclick="return confirm('আপনি কি এই পণ্যটি মুছে ফেলতে চান?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
