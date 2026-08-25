<x-layouts.app :title="$product->getSeoTitle()" :meta-description="$product->getSeoDescription()">
    <section class="py-4">
        <div class="container">
            {{-- ডায়নামিক breadcrumb: হোম → পণ্যসমূহ → ক্যাটাগরি চেইন → পণ্য --}}
            @php
                $crumbs = [
                    ['label' => __('product.common.home'), 'url' => route('home')],
                    ['label' => __('product.common.products'), 'url' => route('products.index')],
                ];
                foreach ($breadcrumb as $cat) {
                    $crumbs[] = ['label' => $cat->name, 'url' => route('categories.show', $cat)];
                }
                $crumbs[] = ['label' => $product->name];
            @endphp
            <x-breadcrumb :items="$crumbs" />

            <div class="row g-4 mt-1">
                {{-- ছবি --}}
                <div class="col-md-6 col-lg-5">
                    @php
                        $mainImage = $product->imageUrl();
                    @endphp
                    <div class="card border-0 shadow-sm overflow-hidden">
                        @if ($mainImage)
                            <img src="{{ $mainImage }}"
                                 alt="{{ $product->imageAltText() }}"
                                 id="main-product-image"
                                 class="w-100"
                                 style="max-height: 420px; object-fit: cover;">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-success-subtle" style="height: 380px;">
                                <span class="fs-1">🌾</span>
                            </div>
                        @endif
                    </div>

                    @if ($product->images->count() > 1 || ($product->images->count() === 1 && ! $product->primaryImage))
                        <div class="d-flex gap-2 mt-3 flex-wrap product-gallery">
                            @foreach ($product->images as $img)
                                <img src="{{ $img->url() }}"
                                     alt="{{ $img->alt_text ?? $product->name }}"
                                     class="rounded border gallery-thumb"
                                     data-full="{{ $img->url() }}"
                                     style="width: 72px; height: 72px; object-fit: cover; cursor: pointer;">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- মূল তথ্য --}}
                <div class="col-md-6 col-lg-7">
                    {{-- ফ্ল্যাগ ব্যাজ --}}
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @if ($product->is_new_arrival)
                            <span class="badge text-bg-success">{{ __('product.flags.new') }}</span>
                        @endif
                        @if ($product->is_featured)
                            <span class="badge text-bg-warning text-dark">{{ __('product.flags.featured') }}</span>
                        @endif
                        @if ($product->is_bestseller)
                            <span class="badge text-bg-danger">{{ __('product.flags.bestseller') }}</span>
                        @endif
                        @if ($product->is_seasonal)
                            <span class="badge text-bg-info">{{ __('product.flags.seasonal') }}</span>
                        @endif
                    </div>

                    <h1 class="h3 fw-bold">{{ $product->name }}</h1>

                    @if ($product->short_description)
                        <p class="text-muted">{{ $product->short_description }}</p>
                    @endif

                    {{-- মূল্য --}}
                    <div class="my-3 d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-semibold">{{ __('product.common.price') }}:</span>
                        <span class="fw-bold text-success fs-3">
                            @price($product->effectivePrice(), $product->unitLabel())
                        </span>
                        @if ($old = $product->oldPrice())
                            <span class="text-muted text-decoration-line-through fs-5">
                                {{ \App\Support\BengaliNumber::money($old) }}
                            </span>
                            <span class="badge text-bg-danger">
                                {{ \App\Support\BengaliNumber::format($product->discountPercent()) }}% {{ __('product.common.discount') }}
                            </span>
                        @endif
                    </div>

                    {{-- স্টক স্ট্যাটাস --}}
                    <p class="mb-3">
                        @if ($product->isInStock())
                            <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('product.stock.in_stock') }}</span>
                        @else
                            <span class="badge text-bg-secondary"><i class="bi bi-x-circle me-1"></i>{{ __('product.stock.out_of_stock') }}</span>
                        @endif
                    </p>

                    {{-- অ্যাকশন — পরবর্তী ফেজে কার্টের সাথে সংযুক্ত হবে --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button type="button"
                                id="add-to-cart-btn"
                                class="btn btn-success btn-lg add-to-cart-btn px-4"
                                data-product-id="{{ $product->id }}"
                                {{ ! $product->isInStock() ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-2"></i>{{ __('product.common.add_to_cart') }}
                        </button>
                        <button type="button"
                                id="buy-now-btn"
                                class="btn btn-outline-success btn-lg px-4"
                                {{ ! $product->isInStock() ? 'disabled' : '' }}>
                            {{ __('product.common.buy_now') }}
                        </button>
                    </div>

                    {{-- দ্রুত তথ্য --}}
                    <ul class="list-group list-group-flush border rounded mb-4">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ __('product.common.category') }}</span>
                            <a href="{{ route('categories.show', $product->category) }}" class="text-decoration-none">
                                {{ $product->category?->name }}
                            </a>
                        </li>
                        @if ($product->unit)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('product.common.unit') }}</span>
                                <span>{{ $product->unitLabel() }}</span>
                            </li>
                        @endif
                        @if ($product->sku)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('product.common.code') }}</span>
                                <code>{{ $product->sku }}</code>
                            </li>
                        @endif
                        @if ($product->origin)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">উৎস</span>
                                <span>{{ $product->origin }}</span>
                            </li>
                        @endif
                        @if ($product->farmer_name)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">কৃষক</span>
                                <span>{{ $product->farmer_name }}</span>
                            </li>
                        @endif
                    </ul>

                    {{-- বিস্তারিত বিবরণ --}}
                    @if ($product->description)
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h2 class="h6 mb-0">{{ __('product.common.description_title') }}</h2>
                            </div>
                            <div class="card-body">
                                <p class="mb-0" style="white-space: pre-line;">{{ $product->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        @once
            <script>
                // ছোট ছবিতে ক্লিক করলে মূল ছবি বদলাবে
                document.querySelectorAll('.gallery-thumb').forEach(function (thumb) {
                    thumb.addEventListener('click', function () {
                        var main = document.getElementById('main-product-image');
                        if (main) {
                            main.src = this.dataset.full;
                        }
                    });
                });
            </script>
        @endonce
    @endpush
</x-layouts.app>
