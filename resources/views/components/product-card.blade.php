@props(['product'])

@php
    $imageUrl = $product->imageUrl();
@endphp

<div class="card product-card h-100 border-0 shadow-sm" data-product-slug="{{ $product->slug }}">
    <a href="{{ route('products.show', $product) }}" class="position-relative text-decoration-none">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $product->imageAltText() }}"
                 class="card-img-top"
                 loading="lazy" decoding="async"
                 style="height: 200px; object-fit: cover;">
        @else
            <div class="d-flex align-items-center justify-content-center bg-success-subtle"
                 style="height: 200px;">
                <span class="fs-1">🌾</span>
            </div>
        @endif

        {{-- ফ্ল্যাগ ব্যাজ --}}
        <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1">
            @if ($product->is_new_arrival)
                <span class="badge text-bg-success">{{ __('product.flags.new') }}</span>
            @endif
            @if ($product->is_featured)
                <span class="badge text-bg-warning text-dark">{{ __('product.flags.featured') }}</span>
            @endif
        </div>
        <div class="position-absolute top-0 end-0 p-2 d-flex flex-column gap-1 align-items-end">
            @if ($product->is_bestseller)
                <span class="badge text-bg-danger">{{ __('product.flags.bestseller') }}</span>
            @endif
            @if ($product->is_seasonal)
                <span class="badge text-bg-info">{{ __('product.flags.seasonal') }}</span>
            @endif
        </div>

        {{-- ছাড়ের হার --}}
        @if ($product->discountPercent() > 0)
            <span class="position-absolute bottom-0 start-0 m-2 badge text-bg-dark">
                {{ \App\Support\BengaliNumber::format($product->discountPercent()) }}% {{ __('product.common.discount') }}
            </span>
        @endif
    </a>

    <div class="card-body d-flex flex-column">
        <small class="text-muted mb-1">
            {{ $product->category?->name }}
        </small>

        <h3 class="h6 card-title mb-2">
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-body">
                {{ $product->name }}
            </a>
        </h3>

        <div class="mt-auto">
            <div class="mb-2">
                @php
                    $displayVariant = $product->displayVariant();
                    $activeCount = $product->activeVariants->count();
                @endphp

                @if ($displayVariant)
                    @if ($displayVariant->oldPrice())
                        <span class="text-muted text-decoration-line-through small me-1">
                            {{ \App\Support\BengaliNumber::money($displayVariant->oldPrice()) }}
                        </span>
                    @endif
                    <span class="fw-bold text-success fs-5">
                        @price($displayVariant->price, $displayVariant->unitLabel())
                    </span>

                    {{-- স্টক ব্যাজ — শুধু প্রয়োজনে (কার্ড পরিচ্ছন্ন রাখতে) --}}
                    @if ($displayVariant->isOutOfStock())
                        <div class="mt-1"><span class="badge text-bg-secondary">{{ __('inventory.statuses.out_of_stock') }}</span></div>
                    @elseif ($displayVariant->isLowStock())
                        <div class="mt-1"><span class="badge text-bg-warning text-dark">{{ __('inventory.statuses.low_stock') }}</span></div>
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
                    <span class="fw-bold text-success fs-5">
                        @price($product->effectivePrice(), $product->unitLabel())
                    </span>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2">
                @php
                    $purchasable = $displayVariant
                        ? $displayVariant->isPurchasable()
                        : $product->isInStock();
                @endphp
                <button type="button"
                        class="btn btn-success btn-sm flex-grow-1 add-to-cart-btn"
                        data-product-id="{{ $product->id }}"
                        data-variant-id="{{ $displayVariant?->id }}"
                        {{ ! $purchasable ? 'disabled' : '' }}
                        title="{{ $purchasable ? '' : __('product.stock.out_of_stock') }}">
                    <i class="bi bi-cart-plus me-1"></i>{{ __('product.common.add_to_cart') }}
                </button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('product.common.view_details') }}
                </a>
            </div>
        </div>
    </div>
</div>
