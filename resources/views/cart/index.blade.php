<x-layouts.app :title="__('cart.cart.title')">
    <section class="py-4">
        <div class="container">
            <x-breadcrumb :items="[
                ['label' => __('product.common.home'), 'url' => route('home')],
                ['label' => __('cart.cart.title')],
            ]" />

            <h1 class="h3 mt-2 mb-4">
                <i class="bi bi-cart2 me-2"></i>{{ __('cart.cart.title') }}
            </h1>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
                </div>
            @endif

            @if (session('error') || $errors->has('cart'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') ?? $errors->first('cart') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
                </div>
            @endif

            @if ($cart->items->isEmpty())
                {{-- খালি কার্ট --}}
                <div class="text-center py-5">
                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                    <p class="h5 mt-3 mb-1">{{ __('cart.cart.empty_title') }}</p>
                    <p class="text-muted">{{ __('cart.cart.empty_message') }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success mt-2">
                        <i class="bi bi-bag me-1"></i>পণ্য দেখুন
                    </a>
                </div>
            @else
                <div class="row g-4">
                    {{-- Item তালিকা --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @foreach ($cart->items as $item)
                                        @php
                                            $variant = $item->variant;
                                            $product = $variant?->product;
                                            $available = $variant?->availableQuantity() ?? 0;
                                            $invalid = ! $product || ! $product->isActive() || ! $variant || ! $variant->isActive();
                                        @endphp
                                        <li class="list-group-item p-3" id="cart-item-{{ $item->id }}">
                                            <div class="d-flex gap-3">
                                                {{-- ছবি --}}
                                                <a href="{{ $product ? route('products.show', $product) : '#' }}" class="flex-shrink-0">
                                                    @if ($product?->imageUrl())
                                                        <img src="{{ $product->imageUrl() }}"
                                                             alt="{{ $product->imageAltText() }}"
                                                             class="rounded"
                                                             loading="lazy"
                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center"
                                                             style="width: 80px; height: 80px;">
                                                            <span class="fs-3">🌾</span>
                                                        </div>
                                                    @endif
                                                </a>

                                                {{-- তথ্য --}}
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex justify-content-between gap-2">
                                                        <div class="min-w-0">
                                                            <a href="{{ $product ? route('products.show', $product) : '#' }}"
                                                               class="fw-semibold text-decoration-none text-body d-block text-truncate">
                                                                {{ $product?->name ?? '—' }}
                                                            </a>
                                                            <small class="text-muted">
                                                                {{ __('cart.cart.variant') }}: {{ $variant?->name ?? '—' }}
                                                                (<code class="small">{{ $variant?->sku }}</code>)
                                                            </small>
                                                        </div>
                                                        <button type="button"
                                                                class="btn btn-sm btn-link text-danger text-decoration-none p-0 flex-shrink-0"
                                                                data-remove-cart-item="{{ route('cart.destroy', $item) }}"
                                                                title="{{ __('cart.cart.remove') }}"
                                                                aria-label="{{ __('cart.cart.remove') }}">
                                                            <i class="bi bi-trash fs-6"></i>
                                                        </button>
                                                    </div>

                                                    {{-- অবস্থা / সতর্কবার্তা --}}
                                                    @if ($invalid)
                                                        <div class="small text-danger mt-1">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ __('cart.cart.unavailable_item') }}
                                                        </div>
                                                    @elseif ($priceChanges->has($item->id))
                                                        <div class="small text-warning-emphasis mt-1">
                                                            <i class="bi bi-exclamation-circle me-1"></i>{{ __('cart.cart.price_changed') }}
                                                            {{ \App\Support\BengaliNumber::money($priceChanges[$item->id]['old_price']) }} →
                                                            <strong>{{ \App\Support\BengaliNumber::money($priceChanges[$item->id]['new_price']) }}</strong>
                                                        </div>
                                                    @elseif ($variant && ! ($variant->inventory?->allow_backorder ?? false) && $available <= 0)
                                                        <div class="small text-danger mt-1">
                                                            <i class="bi bi-x-octagon me-1"></i>{{ __('inventory.statuses.out_of_stock') }}
                                                        </div>
                                                    @endif

                                                    {{-- পরিমাণ + দাম --}}
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                                                        <div class="input-group input-group-sm" style="width: 130px;">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                    data-qty-step="-1"
                                                                    data-update-url="{{ route('cart.update', $item) }}"
                                                                    aria-label="পরিমাণ কমান">−</button>
                                                            <input type="number" min="1"
                                                                   value="{{ $item->quantity }}"
                                                                   class="form-control text-center cart-qty-input"
                                                                   data-update-url="{{ route('cart.update', $item) }}"
                                                                   data-max="{{ $variant && ($variant->inventory?->allow_backorder ?? false) ? '' : $available }}"
                                                                   aria-label="{{ __('cart.cart.quantity') }}">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                    data-qty-step="1"
                                                                    data-update-url="{{ route('cart.update', $item) }}"
                                                                    aria-label="পরিমাণ বাড়ান">+</button>
                                                        </div>

                                                        <div class="text-end">
                                                            <small class="text-muted d-block">
                                                                {{ \App\Support\BengaliNumber::money($item->unit_price) }} × {{ \App\Support\BengaliNumber::format($item->quantity) }}
                                                            </small>
                                                            <span class="fw-bold text-success line-total"
                                                                  data-line-total-for="{{ $item->id }}">
                                                                {{ \App\Support\BengaliNumber::money($item->line_total) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-end py-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-clear-cart="{{ route('cart.clear') }}">
                                    <i class="bi bi-trash3 me-1"></i>{{ __('cart.cart.clear') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- সারসংক্ষেপ --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 90px;">
                            <div class="card-header bg-white"><h2 class="h6 mb-0">সারসংক্ষেপ</h2></div>
                            <div class="card-body">
                                <dl class="mb-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <dt class="fw-normal text-muted small">{{ __('cart.cart.subtotal') }}</dt>
                                        <dd class="mb-0 subtotal-value">{{ \App\Support\BengaliNumber::money($cart->subtotal) }}</dd>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <dt class="fw-semibold">{{ __('cart.cart.total') }}</dt>
                                        <dd class="mb-0 fw-bold text-success fs-5 grand-total">{{ \App\Support\BengaliNumber::money($cart->subtotal) }}</dd>
                                    </div>
                                </dl>
                                <div class="d-grid gap-2 mt-3">
                                    {{-- চেকআউট Phase 08-এ আসবে — কোনো ভুয়া ফাংশনালিটি নয় --}}
                                    <button type="button" class="btn btn-success btn-lg" disabled
                                            title="{{ __('cart.cart.coming_soon') }}">
                                        <i class="bi bi-credit-card me-2"></i>{{ __('cart.cart.checkout') }}
                                    </button>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                                        {{ __('cart.cart.continue_shopping') }}
                                    </a>
                                </div>
                                <p class="small text-muted mt-3 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    চেকআউটের সময় বর্তমান মূল্য ও স্টক পুনঃযাচাই করা হয়।
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
