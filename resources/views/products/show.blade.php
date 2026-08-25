<x-layouts.app :title="$title" :metaDescription="$metaDescription ?? null">
    <div class="container py-4">
        @if ($breadcrumb->isNotEmpty())
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">হোম</a></li>
                    @foreach ($breadcrumb as $crumb)
                        @if ($loop->last)
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb->name }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('categories.show', $crumb->slug) }}">{{ $crumb->name }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="row g-4">
            <div class="col-md-5">
                @if ($product->image)
                    <img src="{{ Storage::url($product->image) }}" class="img-fluid rounded shadow-sm" alt="{{ $product->name }}">
                @else
                    <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center" style="height:400px;">
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <h1 class="h3 mb-2">{{ $product->name }}</h1>

                @if ($product->category)
                    <p class="text-muted mb-3">
                        <a href="{{ route('categories.show', $product->category->slug) }}" class="text-success text-decoration-none">
                            {{ $product->category->name }}
                        </a>
                    </p>
                @endif

                <div class="mb-3">
                    @if ($product->is_featured)
                        <span class="badge text-bg-warning text-dark">বৈশিষ্ট্যযুক্ত</span>
                    @endif
                    @if ($product->is_bestseller)
                        <span class="badge text-bg-danger">বেস্টসেলার</span>
                    @endif
                    @if ($product->is_new_arrival)
                        <span class="badge text-bg-info">নতুন আগমন</span>
                    @endif
                </div>

                <div class="mb-4">
                    @if ($product->variants->isNotEmpty())
                        @php
                            $activeVariants = $product->variants;
                            $minPrice = $activeVariants->min(fn ($v) => $v->hasDiscount() ? $v->discount_price : $v->price);
                            $maxPrice = $activeVariants->max(fn ($v) => $v->hasDiscount() ? $v->discount_price : $v->price);
                        @endphp
                        <span class="fs-3 fw-bold text-success">৳{{ number_format($minPrice, 2) }}</span>
                        @if ($minPrice != $maxPrice)
                            <span class="text-muted fs-5"> — ৳{{ number_format($maxPrice, 2) }}</span>
                        @endif
                    @else
                        @if ($product->hasDiscount())
                            <span class="text-decoration-line-through text-muted fs-5">৳{{ number_format($product->base_price, 2) }}</span>
                            <span class="text-danger fs-3 fw-bold ms-2">৳{{ number_format($product->discount_price, 2) }}</span>
                        @else
                            <span class="fs-3 fw-bold text-success">৳{{ number_format($product->base_price, 2) }}</span>
                        @endif
                        @if ($product->unit)
                            <span class="text-muted ms-1">/ {{ $product->unit }}</span>
                        @endif
                    @endif
                </div>

                @if ($product->variants->isNotEmpty())
                    <div class="mb-4">
                        <h5 class="h6 mb-3">প্যাকেজ নির্বাচন করুন</h5>
                        <div class="list-group variant-selector">
                            @foreach ($product->variants as $variant)
                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <input type="radio" name="selected_variant" value="{{ $variant->id }}"
                                               class="form-check-input me-2 variant-radio"
                                               data-variant-id="{{ $variant->id }}"
                                               {{ $loop->first ? 'checked' : '' }}>
                                        <span class="fw-semibold">{{ $variant->name }}</span>
                                        @if ($variant->weight)
                                            <small class="text-muted ms-1">({{ number_format($variant->weight, 2) }} {{ $variant->unit }})</small>
                                        @endif
                                    </div>
                                    <div>
                                        @if ($variant->hasDiscount())
                                            <span class="text-decoration-line-through text-muted small">৳{{ number_format($variant->price, 2) }}</span>
                                            <span class="text-danger fw-semibold ms-1">৳{{ number_format($variant->discount_price, 2) }}</span>
                                        @else
                                            <span class="fw-semibold text-success">৳{{ number_format($variant->price, 2) }}</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product->short_description)
                    <p class="lead">{{ $product->short_description }}</p>
                @endif

                <form id="addToCartForm" class="mb-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($product->variants->isNotEmpty())
                        <input type="hidden" name="product_variant_id" id="selectedVariantId"
                               value="{{ $product->variants->first()?->id }}">
                    @else
                        <input type="hidden" name="product_variant_id" value="">
                    @endif

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <label for="quantity" class="form-label mb-0 fw-semibold">পরিমাণ:</label>
                        <div class="input-group" style="width:140px;">
                            <button class="btn btn-outline-secondary" type="button" id="qtyMinus">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="quantity" name="quantity"
                                   value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" id="qtyPlus">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="addToCartBtn">
                            <i class="bi bi-cart-plus me-1"></i>কার্টে যোগ করুন
                        </button>
                        @auth
                            <button type="button" class="btn btn-outline-danger btn-lg" id="addToWishlistBtn"
                                    data-product-id="{{ $product->id }}"
                                    data-variant-id="{{ $product->variants->first()?->id }}">
                                <i class="bi bi-heart" id="wishlistIcon"></i>
                            </button>
                        @endauth
                    </div>

                    <div id="cartMessage" class="mt-3" style="display:none;"></div>
                </form>

                @if ($product->sku)
                    <p class="small text-muted">SKU: {{ $product->sku }}</p>
                @endif

                @if ($product->product_type)
                    <p class="small text-muted">ধরন: {{ $product->product_type }}</p>
                @endif

                @if ($product->origin || $product->farmer_name || $product->seasonal_info)
                    <div class="mb-3 p-3 bg-light rounded">
                        @if ($product->origin)
                            <p class="mb-1 small"><strong>উৎস/এলাকা:</strong> {{ $product->origin }}</p>
                        @endif
                        @if ($product->farmer_name)
                            <p class="mb-1 small"><strong>কৃষক/খামার:</strong> {{ $product->farmer_name }}</p>
                        @endif
                        @if ($product->seasonal_info)
                            <p class="mb-0 small"><strong>মৌসুম:</strong> {{ $product->seasonal_info }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($product->description)
            <div class="mt-5">
                <h2 class="h4 mb-3">বিস্তারিত বিবরণ</h2>
                <div class="content">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>
        @endif

        @if ($relatedProducts->isNotEmpty())
            <div class="mt-5">
                <h2 class="h4 mb-3">সম্পর্কিত পণ্য</h2>
                <div class="row g-4">
                    @foreach ($relatedProducts as $related)
                        <div class="col-sm-6 col-lg-3">
                            <x-product-card :product="$related" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const headers = {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            };

            document.querySelectorAll('.variant-radio').forEach(radio => {
                radio.addEventListener('change', function () {
                    document.getElementById('selectedVariantId').value = this.dataset.variantId;
                    const wishlistBtn = document.getElementById('addToWishlistBtn');
                    if (wishlistBtn) {
                        wishlistBtn.dataset.variantId = this.dataset.variantId;
                    }
                });
            });

            document.getElementById('addToWishlistBtn')?.addEventListener('click', function () {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch('{{ route("wishlist.store") }}', {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: btn.dataset.productId,
                        product_variant_id: btn.dataset.variantId || null,
                    }),
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                        btn.classList.remove('btn-outline-danger');
                        btn.classList.add('btn-danger');

                        const badge = document.querySelector('.wishlist-badge');
                        if (badge) {
                            badge.textContent = data.wishlist_count;
                        } else {
                            const navLink = document.querySelector('#wishlist-link');
                            if (navLink) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge';
                                newBadge.style.cssText = 'font-size:0.65rem;';
                                newBadge.textContent = data.wishlist_count;
                                navLink.appendChild(newBadge);
                            }
                        }

                        const msg = document.getElementById('cartMessage');
                        msg.style.display = 'block';
                        msg.className = 'alert alert-success';
                        msg.textContent = data.message;
                        setTimeout(() => { msg.style.display = 'none'; }, 4000);
                    } else {
                        btn.innerHTML = '<i class="bi bi-heart"></i>';
                        const msg = document.getElementById('cartMessage');
                        msg.style.display = 'block';
                        msg.className = 'alert alert-danger';
                        msg.textContent = data.message || 'একটি ত্রুটি ঘটেছে।';
                        setTimeout(() => { msg.style.display = 'none'; }, 4000);
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-heart"></i>';
                });
            });

            document.getElementById('qtyMinus')?.addEventListener('click', function () {
                const input = document.getElementById('quantity');
                const val = parseInt(input.value);
                if (val > 1) input.value = val - 1;
            });

            document.getElementById('qtyPlus')?.addEventListener('click', function () {
                const input = document.getElementById('quantity');
                const val = parseInt(input.value);
                input.value = val + 1;
            });

            document.getElementById('addToCartForm')?.addEventListener('submit', function (e) {
                e.preventDefault();
                const form = this;
                const btn = document.getElementById('addToCartBtn');
                const msg = document.getElementById('cartMessage');

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>যোগ করা হচ্ছে...';

                fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: form.querySelector('[name="product_id"]').value,
                        product_variant_id: form.querySelector('[name="product_variant_id"]').value || null,
                        quantity: form.querySelector('[name="quantity"]').value,
                    }),
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>কার্টে যোগ করুন';

                    msg.style.display = 'block';
                    msg.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
                    msg.textContent = data.message;

                    if (data.success) {
                        const badge = document.querySelector('.mini-cart-badge');
                        if (badge) {
                            badge.textContent = data.cart.item_count;
                        } else {
                            const cartLink = document.querySelector('#mini-cart-dropdown .nav-link');
                            if (cartLink) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mini-cart-badge';
                                newBadge.style.cssText = 'font-size:0.65rem;';
                                newBadge.textContent = data.cart.item_count;
                                cartLink.appendChild(newBadge);
                            }
                        }
                    }

                    setTimeout(() => { msg.style.display = 'none'; }, 4000);
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i>কার্টে যোগ করুন';
                    msg.style.display = 'block';
                    msg.className = 'alert alert-danger';
                    msg.textContent = 'একটি ত্রুটি ঘটেছে। আবার চেষ্টা করুন।';
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>
