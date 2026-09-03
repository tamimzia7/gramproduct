<x-admin.layout title="পণ্যের বিস্তারিত">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $product->name }}</h1>
            <p class="text-muted mb-0">
                @if ($product->category?->parent)
                    {{ $product->category->parent->name }} → {{ $product->category->name }}
                @else
                    {{ $product->category?->name }}
                @endif
            </p>
        </div>
        <div>
            @can('update', $product)
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary me-1">
                    <i class="bi bi-pencil me-1"></i>সম্পাদনা
                </a>
            @endcan
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info me-1" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i>সাইটে দেখুন
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>ফিরে যান
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- মূল তথ্য --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">পণ্যের তথ্য</h2></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width:180px;" class="text-muted">পণ্যের নাম</th>
                            <td>{{ $product->name }}</td>
                        </tr>
                        @if ($product->name_bn)
                            <tr>
                                <th class="text-muted">বাংলা নাম</th>
                                <td>{{ $product->name_bn }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="text-muted">SKU</th>
                            <td><code>{{ $product->sku ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">স্লাগ</th>
                            <td><code>{{ $product->slug }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">ক্যাটাগরি</th>
                            <td>{{ $product->category?->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">একক</th>
                            <td>{{ $product->unitLabel() ?: '—' }}</td>
                        </tr>
                        @if ($product->weight)
                            <tr>
                                <th class="text-muted">ওজন</th>
                                <td>{{ \App\Support\BengaliNumber::format($product->weight) }} কেজি</td>
                            </tr>
                        @endif
                        @if ($product->brand)
                            <tr>
                                <th class="text-muted">ব্র্যান্ড</th>
                                <td>{{ $product->brand }}</td>
                            </tr>
                        @endif
                        @if ($product->tags)
                            <tr>
                                <th class="text-muted">ট্যাগ</th>
                                <td>
                                    @foreach ($product->tagsArray() as $tag)
                                        <span class="badge text-bg-light border">{{ $tag }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th class="text-muted">মূল্য</th>
                            <td>@price($product->effectivePrice(), $product->unitLabel())</td>
                        </tr>
                        <tr>
                            <th class="text-muted">আগের মূল্য</th>
                            <td>{{ $product->compare_at_price ? \App\Support\BengaliNumber::money($product->compare_at_price) : '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">মজুদের সতর্কতা সীমা</th>
                            <td>{{ \App\Support\BengaliNumber::format($product->low_stock_threshold) }} টি</td>
                        </tr>
                        <tr>
                            <th class="text-muted">স্ট্যাটাস</th>
                            <td>
                                @if ($product->is_active)
                                    <span class="badge text-bg-success">সক্রিয়</span>
                                @else
                                    <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                @endif
                                @if ($product->is_featured)
                                    <span class="badge text-bg-warning text-dark">{{ __('product.flags.featured') }}</span>
                                @endif
                                @if ($product->is_bestseller)
                                    <span class="badge text-bg-danger">{{ __('product.flags.bestseller') }}</span>
                                @endif
                                @if ($product->is_new_arrival)
                                    <span class="badge text-bg-success">{{ __('product.flags.new') }}</span>
                                @endif
                                @if ($product->is_seasonal)
                                    <span class="badge text-bg-info">{{ __('product.flags.seasonal') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">স্টক</th>
                            <td>
                                @if ($product->isInStock())
                                    <span class="badge text-bg-success">{{ __('product.stock.in_stock') }}</span>
                                @else
                                    <span class="badge text-bg-secondary">{{ __('product.stock.out_of_stock') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">ভ্যারিয়েন্ট</th>
                            <td>{{ \App\Support\BengaliNumber::format($product->variants->count()) }} টি</td>
                        </tr>
                        <tr>
                            <th class="text-muted">তৈরি</th>
                            <td>{{ $product->created_at->format('d M, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- ছবি --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">ছবিসমূহ ({{ $product->images->count() }})</h2></div>
                <div class="card-body">
                    @if ($product->images->isEmpty())
                        <p class="text-muted mb-0">কোনো ছবি নেই।</p>
                    @else
                        <div class="row g-3">
                            @foreach ($product->images as $image)
                                <div class="col-6 col-md-3">
                                    <img src="{{ $image->url() }}"
                                         alt="{{ $image->alt_text ?? $product->name }}"
                                         class="w-100 rounded border"
                                         style="height:120px;object-fit:cover;">
                                    @if ($image->is_primary)
                                        <span class="badge text-bg-success mt-1">প্রধান ছবি</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ভ্যারিয়েন্টসমূহ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">{{ __('product.variant.list_title') }} ({{ \App\Support\BengaliNumber::format($product->variants->count()) }})</h2>
                    @can('create', \App\Models\ProductVariant::class)
                        <a href="{{ route('admin.products.variants.create', $product) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('product.variant.add') }}
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($product->variants->isEmpty())
                        <p class="text-muted small mb-0">{{ __('product.variant.no_variants') }} {{ __('product.variant.empty_hint') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="small text-muted">
                                        <th>{{ __('product.variant.name') }}</th>
                                        <th>{{ __('product.variant.sku') }}</th>
                                        <th>{{ __('product.variant.quantity') }}</th>
                                        <th>{{ __('product.variant.price') }}</th>
                                        <th>{{ __('product.variant.status') }}</th>
                                        <th style="width: 220px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product->variants as $variant)
                                        @php
                                            $stockLabel = __('product.stock.'.$variant->stock_status->value);
                                        @endphp
                                        <tr class="{{ $variant->isActive() ? '' : 'text-muted' }}">
                                            <td>
                                                <span class="fw-semibold">{{ $variant->name }}</span>
                                                @if ($variant->isDefault() && $variant->isActive())
                                                    <span class="badge text-bg-success ms-1">{{ __('product.variant.default_badge') }}</span>
                                                @endif
                                                @unless ($variant->isActive())
                                                    <span class="badge text-bg-secondary ms-1">{{ __('product.variant.inactive') }}</span>
                                                @endunless
                                            </td>
                                            <td><code>{{ $variant->sku }}</code></td>
                                            <td>{{ $variant->quantityLabel() }}</td>
                                            <td>
                                                @if ($variant->oldPrice())
                                                    <span class="text-muted text-decoration-line-through small me-1">
                                                        {{ \App\Support\BengaliNumber::money($variant->oldPrice()) }}
                                                    </span>
                                                @endif
                                                <span class="fw-semibold">{{ \App\Support\BengaliNumber::money($variant->price) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $variant->isInStock() ? 'text-bg-success' : ($variant->stock_status === \App\Enums\StockStatus::PRE_ORDER ? 'text-bg-warning text-dark' : 'text-bg-secondary') }}">
                                                    {{ $stockLabel }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                                    @can('update', $variant)
                                                        @unless ($variant->isDefault() && $variant->isActive())
                                                            <form method="POST" action="{{ route('admin.products.variants.default', [$product, $variant]) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-outline-success btn-sm"
                                                                        title="{{ __('product.variant.set_default') }}">
                                                                    {{ __('product.variant.set_default') }}
                                                                </button>
                                                            </form>
                                                        @endunless
                                                        <form method="POST" action="{{ route('admin.products.variants.toggle-active', [$product, $variant]) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                                {{ $variant->isActive() ? __('product.variant.deactivate') : __('product.variant.activate') }}
                                                            </button>
                                                        </form>
                                                    @endcan
                                                    @can('update', $variant)
                                                        <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}"
                                                           class="btn btn-outline-primary btn-sm"
                                                           title="{{ __('product.variant.edit') }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $variant)
                                                        <form method="POST"
                                                              action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}"
                                                              onsubmit="return confirm('{{ __('product.variant.delete_confirm') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="মুছে ফেলুন">
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
                    @endif
                </div>
            </div>

            {{-- বিবরণ --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h6 mb-0">বিবরণ</h2></div>
                <div class="card-body">
                    <p class="mb-2"><strong>সংক্ষিপ্ত:</strong> {{ $product->short_description ?: '—' }}</p>
                    <p class="mb-0" style="white-space: pre-line;"><strong>বিস্তারিত:</strong> {{ $product->description ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- SEO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">SEO</h2></div>
                <div class="card-body">
                    <p class="small text-muted mb-1">SEO শিরোনাম</p>
                    <p class="mb-3">{{ $product->getSeoTitle() }}</p>
                    <p class="small text-muted mb-1">SEO বিবরণ</p>
                    <p class="mb-0">{{ $product->getSeoDescription() ?: '—' }}</p>
                </div>
            </div>

            {{-- উৎস --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">উৎস তথ্য</h2></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0 small">
                        <tr>
                            <th class="text-muted">উৎস</th>
                            <td>{{ $product->origin ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">কৃষক</th>
                            <td>{{ $product->farmer_name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">মৌসুমি তথ্য</th>
                            <td>{{ $product->seasonal_info ?: '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
