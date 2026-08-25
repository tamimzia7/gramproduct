<x-layouts.app :title="'আমার ইচ্ছেতালিকা'">
    <div class="container py-4">
        <h1 class="h3 mb-4">
            <i class="bi bi-heart me-2"></i>আমার ইচ্ছেতালিকা
        </h1>

        @if ($wishlistItems->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-heart fs-1 text-muted"></i>
                <p class="mt-3 text-muted">আপনার ইচ্ছেতালিকায় এখনো কোনো পণ্য নেই।</p>
                <a href="{{ route('products.index') }}" class="btn btn-success">
                    <i class="bi bi-bag me-1"></i>পণ্য দেখুন
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>পণ্য</th>
                            <th class="text-end">মূল্য</th>
                            <th class="text-center">অবস্থা</th>
                            <th class="text-center" style="width:200px;">ক্রিয়াকলাপ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wishlistItems as $item)
                            <tr id="wishlist-item-{{ $item->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($item->product->image)
                                            <img src="{{ Storage::url($item->product->image) }}"
                                                 alt="{{ $item->product->name }}"
                                                 class="rounded me-3"
                                                 style="width:60px;height:60px;object-fit:cover;">
                                        @else
                                            <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center me-3"
                                                 style="width:60px;height:60px;">
                                                <i class="bi bi-box-seam text-success"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('products.show', $item->product->slug) }}"
                                               class="text-decoration-none fw-semibold">
                                                {{ $item->product->name }}
                                            </a>
                                            @if ($item->variant)
                                                <br><small class="text-muted">{{ $item->variant->name }}</small>
                                            @endif
                                            @if ($item->product->category)
                                                <br><small class="text-muted">{{ $item->product->category->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    @if ($item->product->isActive())
                                        @if ($item->variant)
                                            @if ($item->variant->hasDiscount())
                                                <span class="text-decoration-line-through text-muted small">৳{{ number_format($item->variant->price, 2) }}</span>
                                                <span class="text-danger fw-semibold">৳{{ number_format($item->variant->discount_price, 2) }}</span>
                                            @else
                                                <span class="fw-semibold text-success">৳{{ number_format($item->variant->price, 2) }}</span>
                                            @endif
                                        @else
                                            @if ($item->product->hasDiscount())
                                                <span class="text-decoration-line-through text-muted small">৳{{ number_format($item->product->base_price, 2) }}</span>
                                                <span class="text-danger fw-semibold">৳{{ number_format($item->product->discount_price, 2) }}</span>
                                            @else
                                                <span class="fw-semibold text-success">৳{{ number_format($item->product->base_price, 2) }}</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if (! $item->product->isActive())
                                        <span class="badge text-bg-secondary">বর্তমানে উপলব্ধ নয়</span>
                                    @else
                                        <span class="badge text-bg-success">উপলব্ধ</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if ($item->product->isActive())
                                            <button class="btn btn-sm btn-success move-to-cart-btn"
                                                    data-item-id="{{ $item->id }}" title="কার্টে নিন">
                                                <i class="bi bi-cart-plus me-1"></i>কার্টে নিন
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger remove-wishlist-btn"
                                                data-item-id="{{ $item->id }}" title="ইচ্ছেতালিকা থেকে সরান">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left me-1"></i>কেনাকাটা চালিয়ে যান
                </a>
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

            document.querySelectorAll('.remove-wishlist-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (confirm('আপনি কি এই পণ্যটি ইচ্ছেতালিকা থেকে সরিয়ে ফেলতে চান?')) {
                        removeWishlistItem(this.dataset.itemId);
                    }
                });
            });

            document.querySelectorAll('.move-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    moveToCart(this.dataset.itemId);
                });
            });

            function removeWishlistItem(itemId) {
                fetch(`/wishlist/${itemId}`, {
                    method: 'DELETE',
                    headers,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`wishlist-item-${itemId}`);
                        if (row) row.remove();
                        const badge = document.querySelector('.wishlist-badge');
                        if (badge) {
                            if (data.wishlist_count > 0) {
                                badge.textContent = data.wishlist_count;
                            } else {
                                badge.remove();
                            }
                        }
                        if (data.wishlist_count === 0) location.reload();
                    } else {
                        alert(data.message || 'একটি ত্রুটি ঘটেছে।');
                    }
                })
                .catch(() => location.reload());
            }

            function moveToCart(itemId) {
                fetch(`/wishlist/${itemId}/move-to-cart`, {
                    method: 'POST',
                    headers,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`wishlist-item-${itemId}`);
                        if (row) row.remove();

                        const wishlistBadge = document.querySelector('.wishlist-badge');
                        if (wishlistBadge) {
                            if (data.wishlist_count > 0) {
                                wishlistBadge.textContent = data.wishlist_count;
                            } else {
                                wishlistBadge.remove();
                            }
                        }

                        const cartBadge = document.querySelector('.mini-cart-badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart.item_count;
                        }

                        if (data.wishlist_count === 0) location.reload();
                    } else {
                        alert(data.message || 'একটি ত্রুটি ঘটেছে।');
                    }
                })
                .catch(() => location.reload());
            }
        });
    </script>
    @endpush
</x-layouts.app>
