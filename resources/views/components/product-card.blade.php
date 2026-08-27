@props(['product'])

@php
    $imageUrl = $product->imageUrl();
    $displayVariant = $product->displayVariant();
    $activeCount = $product->activeVariants->count();
@endphp

<div class="card product-card h-100 w-100 border-0 shadow-sm" data-product-slug="{{ $product->slug }}">
    <a href="{{ route('products.show', $product) }}" class="position-relative text-decoration-none product-card__media">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $product->imageAltText() }}"
                 class="card-img-top product-card__img"
                 loading="lazy" decoding="async"
                 style="height: 200px; object-fit: cover;">
        @else
            <div class="d-flex align-items-center justify-content-center bg-success-subtle product-card__img"
                 style="height: 200px;">
                <span class="fs-1">🌾</span>
            </div>
        @endif

        {{-- ফ্ল্যাগ ব্যাজ — ডায়নামিক; applicable না থাকলে কোনো badge দেখায় না --}}
        @if ($product->is_bestseller || $product->is_featured || $product->is_new_arrival || $product->is_seasonal)
            <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1 align-items-start">
                @if ($product->is_bestseller)
                    <span class="badge text-bg-danger">{{ __('home.badges.bestseller') }}</span>
                @endif
                @if ($product->is_featured)
                    <span class="badge text-bg-warning text-dark">{{ __('home.badges.featured') }}</span>
                @endif
                @if ($product->is_new_arrival)
                    <span class="badge text-bg-success">{{ __('product.flags.new') }}</span>
                @endif
                @if ($product->is_seasonal)
                    <span class="badge text-bg-info">{{ __('product.flags.seasonal') }}</span>
                @endif
            </div>
        @endif

        {{-- Wishlist ♡ — existing system; guest → bengali login prompt toast --}}
        <button type="button"
                class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-2 card-wishlist-btn"
                data-wishlist-toggle
                data-product-id="{{ $product->id }}"
                data-saved="0"
                title="{{ __('cart.wishlist.add') }}"
                aria-label="{{ __('cart.wishlist.add') }}">
            <i class="bi bi-heart"></i>
        </button>

        {{-- ছাড়ের হার --}}
        @if ($displayVariant?->discountPercent() > 0 || (! $displayVariant && $product->discountPercent() > 0))
            @php
                $discountPercent = $displayVariant?->discountPercent() ?: $product->discountPercent();
            @endphp
            <span class="position-absolute bottom-0 start-0 m-2 badge text-bg-dark">
                {{ \App\Support\BengaliNumber::format($discountPercent) }}% {{ __('product.common.discount') }}
            </span>
        @endif
    </a>

    <div class="card-body d-flex flex-column p-3">
        {{-- meta — category (database-backed) --}}
        <small class="text-muted mb-1 text-truncate">
            {{ $product->category?->name }}
        </small>

        {{-- নাম — max 2 line clamp --}}
        <h3 class="h6 card-title mb-2 product-card__name">
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-body">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto">
            <div class="mb-2">
                @php
                    $purchasable = $displayVariant
                        ? $displayVariant->isPurchasable()
                        : $product->isInStock();
                @endphp

                @if ($displayVariant)
                    @if ($displayVariant->oldPrice())
                        <span class="text-muted text-decoration-line-through small me-1">
                            {{ \App\Support\BengaliNumber::money($displayVariant->oldPrice()) }}
                        </span>
                    @endif
                    <span class="fw-bold text-success fs-6">
                        @price($displayVariant->price, $displayVariant->unitLabel())
                    </span>

                    {{-- স্টক ব্যাজ — শুধু প্রয়োজনে (কার্ড পরিচ্ছন্ন রাখতে) --}}
                    @if ($displayVariant->isOutOfStock())
                        <div class="mt-1"><span class="badge text-bg-secondary">{{ __('inventory.statuses.out_of_stock') }}</span></div>
                    @elseif ($displayVariant->isLowStock())
                        <div class="mt-1"><span class="badge text-bg-warning text-dark">{{ __('inventory.statuses.low_stock_left', ['count' => \App\Support\BengaliNumber::format($displayVariant->availableQuantity())]) }}</span></div>
                    @endif

                    @if ($activeCount > 1)
                        <div class="mt-1">
                            <span class="badge text-bg-light text-success border">
                                {{ __('product.variant.variants_count', ['count' => \App\Support\BengaliNumber::format($activeCount)]) }}
                            </span>
                        </div>
                    @endif
                @else
                    @if ($old = $product->oldPrice())
                        <span class="text-muted text-decoration-line-through small me-1">
                            {{ \App\Support\BengaliNumber::money($old) }}
                        </span>
                    @endif
                    <span class="fw-bold text-success fs-6">
                        @price($product->effectivePrice(), $product->unitLabel())
                    </span>
                @endif
            </div>

            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-success btn-sm flex-grow-1 add-to-cart-btn"
                        data-add-to-cart="{{ route('cart.store') }}"
                        data-product-id="{{ $product->id }}"
                        data-variant-id="{{ $displayVariant?->id }}"
                        {{ ! $purchasable ? 'disabled' : '' }}
                        title="{{ $purchasable ? '' : __('inventory.statuses.out_of_stock') }}">
                    <i class="bi bi-cart-plus me-1"></i>{{ __('product.common.add_to_cart') }}
                </button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm px-2"
                   aria-label="{{ __('product.common.view_details') }}">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
        </div>
    </div>
</div>
