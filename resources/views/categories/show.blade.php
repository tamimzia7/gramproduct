<x-layouts.app
    :title="$category->getSeoTitleAttribute() ?? $category->name"
    :meta-description="$category->getSeoDescriptionAttribute()">
    <section class="py-4">
        <div class="container">
            {{-- ডায়নামিক breadcrumb: হোম → ক্যাটাগরি চেইন --}}
            @php
                $crumbs = [
                    ['label' => __('product.common.home'), 'url' => route('home')],
                    ['label' => __('product.common.categories'), 'url' => route('categories.index')],
                ];
                foreach ($breadcrumb as $cat) {
                    $crumbs[] = [
                        'label' => $cat->name,
                        'url' => $cat->is($category) ? null : route('categories.show', $cat),
                    ];
                }
            @endphp
            <x-breadcrumb :items="$crumbs" />

            {{-- ক্যাটাগরি হেডার --}}
            <div class="mt-2 mb-4">
                <h1 class="h3 mb-1">{{ $category->name }}</h1>
                @if ($category->description)
                    <p class="text-muted mb-0">{{ $category->description }}</p>
                @endif
            </div>

            {{-- সাব-ক্যাটাগরি — "এই ক্যাটাগরির ধরন" --}}
            @if ($subcategories->isNotEmpty())
                <div class="mb-4">
                    <h2 class="h6 text-muted mb-2">এই ক্যাটাগরির ধরন</h2>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($subcategories as $sub)
                            <a href="{{ route('categories.show', $sub) }}"
                               class="btn btn-sm {{ $loop->first ? '' : '' }} btn-outline-success rounded-pill px-3">
                                {{ $sub->name }}
                                <span class="badge text-bg-light border ms-1">{{ \App\Support\BengaliNumber::format($sub->products_count) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ফিল্টার ও সাজানো --}}
            @if ($products->isNotEmpty())
                <form method="GET" action="{{ route('categories.show', $category) }}" class="row g-2 align-items-end mb-4">
                    <div class="col-md-5 col-lg-4">
                        <label for="q" class="form-label small text-muted">অনুসন্ধান</label>
                        <input type="text" id="q" name="q" value="{{ old('q', $search) }}"
                               class="form-control form-control-sm"
                               placeholder="{{ __('product.common.search_placeholder') }}">
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label for="sort" class="form-label small text-muted">{{ __('product.common.sort') }}</label>
                        <select id="sort" name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="" {{ ! request('sort') ? 'selected' : '' }}>{{ __('product.common.sort_relevant') }}</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>{{ __('product.common.sort_popular') }}</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('product.common.sort_price_asc') }}</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('product.common.sort_price_desc') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-funnel me-1"></i>{{ __('product.common.filter') }}
                        </button>
                    </div>
                </form>
            @endif

            {{-- পণ্যের গ্রিড --}}
            @if ($products->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-basket fs-1 text-muted"></i>
                    <p class="h6 mt-3 mb-1">এই ক্যাটাগরিতে এখনো কোনো পণ্য যোগ করা হয়নি।</p>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-success btn-sm mt-2">
                        {{ __('product.common.all_products') }}
                    </a>
                </div>
            @else
                <p class="small text-muted mb-3">
                    মোট {{ \App\Support\BengaliNumber::format($products->total()) }} টি পণ্য
                </p>
                <x-product-grid :products="$products" />

                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
