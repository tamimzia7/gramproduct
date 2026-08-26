<x-layouts.app :title="__('cart.wishlist.label')">
    <section class="py-4">
        <div class="container">
            <x-breadcrumb :items="[
                ['label' => __('product.common.home'), 'url' => route('home')],
                ['label' => 'আমার ইচ্ছেতালিকা'],
            ]" />

            <h1 class="h3 mt-2 mb-4">
                <i class="bi bi-heart me-2"></i>আমার ইচ্ছেতালিকা
            </h1>

            @if ($wishlistItems->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-heart fs-1 text-muted"></i>
                    <p class="h5 mt-3 mb-1">আপনার ইচ্ছেতালিকা খালি</p>
                    <p class="text-muted">আপনার পছন্দের পণ্যগুলো এখানে সংরক্ষণ করুন।</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success mt-2">
                        <i class="bi bi-bag me-1"></i>পণ্য দেখুন
                    </a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($wishlistItems as $item)
                        @php
                            $product = $item->product;
                            $displayVariant = $product?->displayVariant();
                            $available = $product && $product->isActive() && $displayVariant?->isPurchasable();
                            $unavailableText = 'এই পণ্যটি বর্তমানে পাওয়া যাচ্ছে না।';
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3" id="wishlist-item-{{ $item->id }}">
                            <div class="card h-100 border-0 shadow-sm product-card">
                                <a href="{{ route('products.show', ['product' => $product->slug]) }}" class="position-relative text-decoration-none">
                                    @if ($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}"
                                             alt="{{ $product->imageAltText() }}"
                                             class="card-img-top" loading="lazy"
                                             style="height: 180px; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-success-subtle"
                                             style="height: 180px;">
                                            <span class="fs-1">🌾</span>
                                        </div>
                                    @endif
                                </a>

                                <div class="card-body d-flex flex-column p-3">
                                    <small class="text-muted mb-1">{{ $product->category?->name }}</small>
                                    <h3 class="h6 card-title mb-2">
                                        <a href="{{ route('products.show', ['product' => $product->slug]) }}"
                                           class="text-decoration-none text-body">
                                            {{ $product->name }}
                                        </a>
                                    </h3>

                                    <div class="mt-auto">
                                        @if ($product->isActive() && $displayVariant)
                                            <div class="mb-2">
                                                <span class="fw-bold text-success fs-6">
                                                    @price($displayVariant->price, $displayVariant->unitLabel())
                                                </span>
                                            </div>
                                            <div class="small mb-2">
                                                @if ($available)
                                                    <span class="text-success">{{ $displayVariant->stockLabel() }}</span>
                                                @elseif ($product->hasActiveVariants())
                                                    <span class="text-danger">{{ __('inventory.statuses.out_of_stock') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="small text-danger mb-2">
                                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $unavailableText }}
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap gap-2">
                                            @if ($available)
                                                <button type="button"
                                                        class="btn btn-success btn-sm flex-grow-1"
                                                        data-add-to-cart="{{ route('cart.store') }}"
                                                        data-variant-id="{{ $displayVariant->id }}">
                                                    <i class="bi bi-cart-plus me-1"></i>কার্টে নিন
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                                    <i class="bi bi-cart-plus me-1"></i>কার্টে নিন
                                                </button>
                                            @endif
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    data-remove-wishlist-item="{{ route('wishlist.destroy', $item) }}"
                                                    title="{{ __('cart.wishlist.remove') }}"
                                                    aria-label="{{ __('cart.wishlist.remove') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
