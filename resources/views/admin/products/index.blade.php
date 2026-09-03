<x-admin.layout title="পণ্যসমূহ">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">পণ্যসমূহ</h1>
            <p class="text-muted mb-0">সব পণ্য পরিচালনা করুন</p>
        </div>
        <div>
            @can('create', App\Models\Product::class)
                <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i>নতুন পণ্য যোগ করুন
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ফিল্টার --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}" id="filterForm">
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
                        <label for="stock" class="form-label small text-muted">স্টক অবস্থা</label>
                        <select id="stock" name="stock" class="form-select">
                            <option value="">সব</option>
                            <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>স্টকে আছে</option>
                            <option value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>স্টক শেষ</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <div class="form-check">
                            <input type="checkbox" name="trashed" value="1" class="form-check-input" id="showTrashed"
                                   {{ request('trashed') ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label small" for="showTrashed">মুছে ফেলা</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-grid gap-1">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-search me-1"></i>ফিল্টার
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">রিসেট</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- বাল্ক অ্যাকশন --}}
    <form method="POST" action="{{ route('admin.products.bulk-action') }}" id="bulkForm">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput" value="">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" class="form-check-input" id="selectAll" title="সব নির্বাচন করুন">
                    <span class="text-muted small" id="selectedCount"></span>
                </div>
                <div class="d-flex gap-1" id="bulkActions" style="display:none;">
                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="document.getElementById('bulkActionInput').value='activate'" title="সক্রিয় করুন">
                        <i class="bi bi-check-circle me-1"></i>সক্রিয়
                    </button>
                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="document.getElementById('bulkActionInput').value='deactivate'" title="নিষ্ক্রিয় করুন">
                        <i class="bi bi-x-circle me-1"></i>নিষ্ক্রিয়
                    </button>
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('bulkActionInput').value='delete'; return confirm('আপনি কি নির্বাচিত পণ্যগুলো মুছে ফেলতে চান?')" title="মুছে ফেলুন">
                        <i class="bi bi-trash me-1"></i>মুছে ফেলুন
                    </button>
                </div>
            </div>

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
                                    <th style="width:40px;"></th>
                                    <th>ছবি</th>
                                    <th>পণ্যের নাম</th>
                                    <th>SKU</th>
                                    <th>ক্যাটাগরি</th>
                                    <th>মূল্য</th>
                                    <th class="text-center">স্টক</th>
                                    <th class="text-center">অবস্থা</th>
                                    <th class="text-center">বিশেষ</th>
                                    <th class="text-center">নতুন</th>
                                    <th>তারিখ</th>
                                    <th class="text-end">কার্যক্রম</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    @php
                                        $lowStock = false;
                                        foreach ($product->activeVariants as $v) {
                                            if ($v->inventory && $v->inventory->available_quantity > 0 && $v->inventory->available_quantity <= $product->low_stock_threshold) {
                                                $lowStock = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $product->trashed() ? 'table-warning' : '' }}">
                                        <td>
                                            <input type="checkbox" name="ids[]" value="{{ $product->id }}"
                                                   class="form-check-input product-checkbox">
                                        </td>
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
                                            @if ($product->name_bn)
                                                <br><small class="text-muted">{{ $product->name_bn }}</small>
                                            @endif
                                        </td>
                                        <td><code>{{ $product->sku ?? '—' }}</code></td>
                                        <td>{{ $product->category?->name }}</td>
                                        <td class="fw-semibold">@price($product->effectivePrice(), $product->unitLabel())</td>
                                        <td class="text-center">
                                            @if ($lowStock)
                                                <span class="badge text-bg-warning text-dark" title="মজুদ কম">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>সতর্ক
                                                </span>
                                            @elseif ($product->isInStock())
                                                <span class="badge text-bg-success">স্টকে আছে</span>
                                            @else
                                                <span class="badge text-bg-secondary">স্টক শেষ</span>
                                            @endif
                                        </td>
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
                                            @if ($product->is_new_arrival)
                                                <span class="badge text-bg-info">নতুন</span>
                                            @else
                                                <span class="text-muted">—</span>
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
                                                @if ($product->trashed())
                                                    <form method="POST" action="{{ route('admin.products.restore', $product->id) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-success" title="পুনরুদ্ধার">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    </form>
                                                @else
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
                                                @endif
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
    </form>

    @push('scripts')
    <script>
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        function updateBulkUI() {
            const checked = document.querySelectorAll('.product-checkbox:checked').length;
            bulkActions.style.display = checked > 0 ? 'flex' : 'none';
            selectedCount.textContent = checked > 0 ? checked + 'টি নির্বাচিত' : '';
        }

        selectAll?.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkUI();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', updateBulkUI));
    </script>
    @endpush
</x-admin.layout>
