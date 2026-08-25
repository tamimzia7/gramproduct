<x-admin.layout title="ক্যাটাগরির বিস্তারিত">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $category->name }}</h1>
            <p class="text-muted mb-0">
                @foreach ($breadcrumb as $i => $crumb)
                    @if ($i > 0) <i class="bi bi-chevron-right mx-1 small"></i> @endif
                    <a href="{{ route('admin.categories.show', $crumb->id) }}" class="text-decoration-none">{{ $crumb->name }}</a>
                @endforeach
            </p>
        </div>
        <div>
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline-primary me-1">
                <i class="bi bi-pencil me-1"></i>সম্পাদনা
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>ফিরে যান
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- ক্যাটাগরির বিবরণ --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">ক্যাটাগরির তথ্য</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width:180px;" class="text-muted">নাম</th>
                            <td>{{ $category->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Slug</th>
                            <td><code>{{ $category->slug }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">প্যারেন্ট</th>
                            <td>
                                @if ($category->parent)
                                    <a href="{{ route('admin.categories.show', $category->parent) }}">
                                        {{ $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">— মূল ক্যাটাগরি —</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">বিবরণ</th>
                            <td>{{ $category->description ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">স্ট্যাটাস</th>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge text-bg-success">সক্রিয়</span>
                                @else
                                    <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">ফিচার্ড</th>
                            <td>
                                @if ($category->is_featured)
                                    <span class="badge text-bg-warning text-dark">
                                        <i class="bi bi-star-fill me-1"></i>ফিচার্ড
                                    </span>
                                @else
                                    <span class="text-muted">না</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">প্রদর্শনের ক্রম</th>
                            <td>{{ $category->sort_order }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">তৈরি</th>
                            <td>{{ $category->created_at->format('d M, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">সর্বশেষ আপডেট</th>
                            <td>{{ $category->updated_at->format('d M, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- পণ্যসমূহ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">পণ্যসমূহ ({{ $category->products()->count() }})</h6>
                </div>
                <div class="card-body">
                    @if ($category->products->isEmpty())
                        <p class="text-muted mb-0">এই ক্যাটাগরিতে এখনো কোনো পণ্য যোগ করা হয়নি।</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>পণ্য</th>
                                        <th class="text-center">স্ট্যাটাস</th>
                                        <th class="text-end">মূল্য</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($category->products->take(10) as $product)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.products.show', $product) }}" class="text-decoration-none">
                                                    {{ $product->name }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @if ($product->is_active)
                                                    <span class="badge text-bg-success">সক্রিয়</span>
                                                @else
                                                    <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ Number::currency($product->price) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- সাইডবার --}}
        <div class="col-md-4">
            {{-- ছবি --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">ছবি</h6>
                </div>
                <div class="card-body text-center">
                    @if ($category->image)
                        <img src="{{ Storage::url($category->image) }}"
                             alt="{{ $category->name }}"
                             class="rounded"
                             style="max-width:200px;max-height:200px;object-fit:cover;">
                    @else
                        <div class="bg-success-subtle rounded d-inline-flex align-items-center justify-content-center"
                             style="width:120px;height:120px;">
                            <i class="bi bi-image text-success" style="font-size:2rem;"></i>
                        </div>
                        <p class="text-muted small mt-2 mb-0">কোনো ছবি নেই</p>
                    @endif
                </div>
            </div>

            {{-- সাব-ক্যাটাগরি --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">সাব-ক্যাটাগরি ({{ $category->children->count() }})</h6>
                </div>
                <div class="card-body">
                    @if ($category->children->isEmpty())
                        <p class="text-muted small mb-0">কোনো সাব-ক্যাটাগরি নেই।</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($category->children as $child)
                                <li class="mb-2">
                                    <a href="{{ route('admin.categories.show', $child) }}" class="text-decoration-none">
                                        {{ $child->name }}
                                    </a>
                                    @if ($child->products_count > 0)
                                        <span class="badge bg-light text-dark ms-1">{{ $child->products_count }} পণ্য</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- SEO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">SEO</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="small text-muted">SEO শিরোনাম</label>
                        <p class="mb-0">{{ $category->seo_title ?: $category->name }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted">SEO বিবরণ</label>
                        <p class="mb-0">{{ $category->seo_description ?: $category->description ?: '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
