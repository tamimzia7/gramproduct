<x-layouts.app :title="$metaTitle ?? __('product.common.all_products')">
    <section class="py-4">
        <div class="container">
            <x-breadcrumb :items="[
                ['label' => __('product.common.home'), 'url' => route('home')],
                ['label' => __('product.common.products')],
            ]" />

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h1 class="h3 mb-1">পণ্যসমূহ</h1>
                    <p class="text-muted mb-0">গ্রাম, মাঠ ও নদীর খাঁটি পণ্য কিনুন</p>
                </div>
                <span class="badge text-bg-light border">
                    মোট {{ \App\Support\BengaliNumber::format($products->total()) }} টি পণ্য
                </span>
            </div>

            {{-- ফিল্টার --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label for="q" class="form-label small text-muted">অনুসন্ধান</label>
                                <input type="text" id="q" name="q" class="form-control"
                                       placeholder="{{ __('product.common.search_placeholder') }}"
                                       value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label small text-muted">{{ __('product.common.categories') }}</label>
                                <select id="category" name="category" class="form-select">
                                    <option value="">সব ক্যাটাগরি</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}"
                                                {{ request('category') === $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort" class="form-label small text-muted">{{ __('product.common.sort') }}</label>
                                <select id="sort" name="sort" class="form-select">
                                    <option value="" {{ ! request('sort') ? 'selected' : '' }}>{{ __('product.common.sort_relevant') }}</option>
                                    <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>{{ __('product.common.sort_popular') }}</option>
                                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('product.common.sort_price_asc') }}</option>
                                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('product.common.sort_price_desc') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-search me-1"></i>{{ __('product.common.filter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- পণ্যের গ্রিড --}}
            @if ($products->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-basket fs-1 text-muted"></i>
                    @if (trim((string) $search) !== '')
                        <h2 class="h5 mt-3">{{ __('product.common.no_search_results') }}</h2>
                    @else
                        <h2 class="h5 mt-3">{{ __('product.common.no_products') }}</h2>
                    @endif
                    <p class="text-muted">{{ __('product.common.no_products_hint') }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-success btn-sm mt-1">
                        {{ __('product.common.all_products') }}
                    </a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
