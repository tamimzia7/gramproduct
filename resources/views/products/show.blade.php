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
                                 loading="lazy" decoding="async"
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

                    @php
                        $displayVariant = $product->displayVariant();
                        $hasVariants = $product->variants()->exists();
                        // প্রতিটি ভ্যারিয়েন্টের স্টক-স্টেট server-এই নির্ণীত — JS-এ নতুন রিকুয়েস্ট লাগে না
                        $variantData = $product->activeVariants->map(fn ($v) => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'sku' => $v->sku,
                            'price' => \App\Support\BengaliNumber::money($v->price),
                            'old_price' => $v->oldPrice() ? \App\Support\BengaliNumber::money($v->oldPrice()) : null,
                            'discount_percent' => $v->discountPercent(),
                            'stock_label' => $v->stockLabel(),
                            'stock_state' => $v->stock_status === \App\Enums\StockStatus::PRE_ORDER ? 'pre_order'
                                : ($v->isOutOfStock() ? 'out_of_stock' : ($v->isLowStock() ? 'low_stock' : 'in_stock')),
                            'in_stock' => $v->isInStock(),
                            'purchasable' => $v->isPurchasable(),
                            'quantity_label' => $v->quantityLabel(),
                        ])->values()->all();
                    @endphp

                    {{-- ভ্যারিয়েন্ট নির্বাচন --}}
                    @if ($product->hasActiveVariants())
                        <div class="my-3">
                            <p class="fw-semibold mb-2">{{ __('product.variant.select_variant') }}</p>
                            <div class="d-flex flex-wrap gap-2" id="variant-selector" role="radiogroup"
                                 aria-label="{{ __('product.variant.select_variant') }}">
                                @foreach ($product->activeVariants as $variant)
                                    <input type="radio"
                                           class="btn-check variant-radio"
                                           name="variant_option"
                                           id="variant-option-{{ $variant->id }}"
                                           autocomplete="off"
                                           data-variant-id="{{ $variant->id }}"
                                           aria-label="{{ $variant->quantityLabel() }}"
                                           @checked($variant->is($displayVariant))>
                                    <label class="btn btn-outline-success px-3" for="variant-option-{{ $variant->id }}">
                                        {{ $variant->name }}
                                        @if ($variant->isOutOfStock())
                                            <small class="d-block text-danger fw-normal">({{ __('inventory.statuses.out_of_stock') }})</small>
                                        @elseif ($variant->isLowStock())
                                            <small class="d-block text-warning-emphasis fw-normal">({{ __('inventory.statuses.low_stock_left', ['count' => \App\Support\BengaliNumber::format($variant->availableQuantity())]) }})</small>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- মূল্য --}}
                    <div class="my-3 d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-semibold">{{ __('product.common.price') }}:</span>
                        @if ($product->hasActiveVariants())
                            <span class="fw-bold text-success fs-3" id="selected-variant-price">
                                {{ \App\Support\BengaliNumber::money($displayVariant->price) }}
                            </span>
                            <span class="text-muted text-decoration-line-through fs-5 d-none" id="selected-variant-old-price"></span>
                            <span class="badge text-bg-danger d-none" id="selected-variant-discount"></span>
                        @else
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
                        @endif
                    </div>

                    {{-- স্টক স্ট্যাটাস --}}
                    <p class="mb-3">
                        @if ($product->hasActiveVariants())
                            @php
                                $initialState = $displayVariant->stock_status === \App\Enums\StockStatus::PRE_ORDER ? 'pre_order'
                                    : ($displayVariant->isOutOfStock() ? 'out_of_stock' : ($displayVariant->isLowStock() ? 'low_stock' : 'in_stock'));
                                $stockBadgeClasses = [
                                    'in_stock' => 'text-bg-success',
                                    'low_stock' => 'text-bg-warning text-dark',
                                    'out_of_stock' => 'text-bg-secondary',
                                    'pre_order' => 'text-bg-warning text-dark',
                                ];
                            @endphp
                            <span class="badge {{ $stockBadgeClasses[$initialState] }}" id="selected-variant-stock">
                                {{ $displayVariant->stockLabel() }}
                            </span>
                        @elseif (! $hasVariants)
                            @if ($product->isInStock())
                                <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>{{ __('product.stock.in_stock') }}</span>
                            @else
                                <span class="badge text-bg-secondary"><i class="bi bi-x-circle me-1"></i>{{ __('product.stock.out_of_stock') }}</span>
                            @endif
                        @endif
                    </p>

                    {{-- পণ্যটির কোনো সক্রিয় ভ্যারিয়েন্ট নেই — অসুপলব্ধ অবস্থা --}}
                    @if ($hasVariants && ! $product->hasActiveVariants())
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-1"></i>{{ __('product.variant.unavailable') }}
                        </div>
                    @endif

                    {{-- অ্যাকশন — কার্ট/ইচ্ছেতালিকা ইন্টিগ্রেশন --}}
                    @php
                        $canBuy = $product->hasActiveVariants()
                            ? $displayVariant->isPurchasable()
                            : (! $hasVariants && $product->isInStock());
                        $savedInWishlist = auth()->check() && \App\Models\WishlistItem::query()
                            ->where('user_id', auth()->id())
                            ->where('product_id', $product->id)
                            ->exists();
                    @endphp
                    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
                        <button type="button"
                                id="add-to-cart-btn"
                                class="btn btn-success btn-lg add-to-cart-btn px-4"
                                data-add-to-cart="{{ route('cart.store') }}"
                                data-product-id="{{ $product->id }}"
                                data-variant-id="{{ $displayVariant?->id }}"
                                {{ ! $canBuy ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-2"></i>{{ __('product.common.add_to_cart') }}
                        </button>
                        <button type="button"
                                id="buy-now-btn"
                                class="btn btn-outline-success btn-lg px-4"
                                title="{{ __('cart.cart.coming_soon') }}"
                                {{ ! $canBuy ? 'disabled' : '' }}>
                            {{ __('product.common.buy_now') }}
                        </button>
                        <button type="button"
                                id="wishlist-toggle-btn"
                                class="btn {{ $savedInWishlist ? 'btn-danger' : 'btn-outline-danger' }} btn-lg"
                                data-wishlist-toggle
                                data-product-id="{{ $product->id }}"
                                data-saved="{{ $savedInWishlist ? '1' : '0' }}"
                                aria-label="{{ $savedInWishlist ? __('cart.wishlist.remove') : __('cart.wishlist.add') }}">
                            <i class="bi {{ $savedInWishlist ? 'bi-heart-fill' : 'bi-heart' }} me-1"></i>
                            <span>{{ $savedInWishlist ? __('cart.wishlist.remove') : __('cart.wishlist.add') }}</span>
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
                        @if ($product->hasActiveVariants())
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('product.common.unit') }}</span>
                                <span id="selected-variant-quantity">{{ $displayVariant->quantityLabel() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">SKU</span>
                                <code id="selected-variant-sku">{{ $displayVariant->sku }}</code>
                            </li>
                        @else
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

            {{-- সম্পর্কিত পণ্য — একই ক্যাটাগরি --}}
            @if ($relatedProducts->isNotEmpty())
                <div class="mt-5 pt-4 border-top">
                    <x-section-header :title="__('home.related.title')"
                                      :view-all-url="route('categories.show', $product->category)"
                                      :view-all-text="__('home.sections.view_all')" />
                    <x-product-grid :products="$relatedProducts" />
                </div>
            @endif
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

        @if ($product->hasActiveVariants())
            <script type="application/json" id="variant-payload">{!! json_encode($variantData, JSON_UNESCAPED_UNICODE) !!}</script>
            @once
                <script>
                    // ভ্যারিয়েন্ট নির্বাচন — server-এ প্রি-ফরম্যাট করা ডেটা; নতুন কোনো রিকুয়েস্ট হয় না
                    (function () {
                        var payloadEl = document.getElementById('variant-payload');
                        if (! payloadEl) return;

                        var variants = {};
                        try {
                            JSON.parse(payloadEl.textContent).forEach(function (v) { variants[v.id] = v; });
                        } catch (e) {
                            return;
                        }

                        var elPrice = document.getElementById('selected-variant-price');
                        var elOld = document.getElementById('selected-variant-old-price');
                        var elDiscount = document.getElementById('selected-variant-discount');
                        var elStock = document.getElementById('selected-variant-stock');
                        var elQtyLabel = document.getElementById('selected-variant-quantity');
                        var elSku = document.getElementById('selected-variant-sku');
                        var addBtn = document.getElementById('add-to-cart-btn');
                        var buyBtn = document.getElementById('buy-now-btn');

                        function apply(variant) {
                            if (! variant) return;

                            if (elPrice) elPrice.textContent = variant.price;

                            if (elOld) {
                                if (variant.old_price) {
                                    elOld.textContent = variant.old_price;
                                    elOld.classList.remove('d-none');
                                } else {
                                    elOld.textContent = '';
                                    elOld.classList.add('d-none');
                                }
                            }

                            if (elDiscount) {
                                if (variant.discount_percent > 0) {
                                    elDiscount.textContent = variant.discount_percent + '% {{ __('product.common.discount') }}';
                                    elDiscount.classList.remove('d-none');
                                } else {
                                    elDiscount.textContent = '';
                                    elDiscount.classList.add('d-none');
                                }
                            }

                            if (elStock) {
                                var badgeClasses = {
                                    in_stock: 'text-bg-success',
                                    low_stock: 'text-bg-warning text-dark',
                                    out_of_stock: 'text-bg-secondary',
                                    pre_order: 'text-bg-warning text-dark'
                                };
                                elStock.className = 'badge';
                                elStock.classList.add(badgeClasses[variant.stock_state] || 'text-bg-secondary');
                                elStock.textContent = variant.stock_label;
                            }

                            if (elQtyLabel) elQtyLabel.textContent = variant.quantity_label;
                            if (elSku && variant.sku) elSku.textContent = variant.sku;

                            [addBtn, buyBtn].forEach(function (btn) {
                                if (btn) btn.disabled = ! variant.purchasable;
                            });

                            if (addBtn) addBtn.dataset.variantId = variant.id;
                        }

                        document.querySelectorAll('.variant-radio').forEach(function (radio) {
                            radio.addEventListener('change', function () {
                                if (this.checked) apply(variants[this.dataset.variantId]);
                            });
                        });
                    })();
                </script>
            @endonce
        @endif
    @endpush
</x-layouts.app>
