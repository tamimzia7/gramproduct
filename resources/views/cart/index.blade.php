<x-layouts.app :title="'শপিং কার্ট'">
    <div class="container py-4">
        <h1 class="h3 mb-4">
            <i class="bi bi-cart3 me-2"></i>শপিং কার্ট
        </h1>

        @if (! $cart || $cart->items->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-cart-x fs-1 text-muted"></i>
                <p class="mt-3 text-muted">আপনার কার্ট বর্তমানে খালি।</p>
                <a href="{{ route('products.index') }}" class="btn btn-success">
                    <i class="bi bi-bag me-1"></i>কেনাকাটা চালিয়ে যান
                </a>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>পণ্য</th>
                                            <th class="text-center" style="width:120px;">পরিমাণ</th>
                                            <th class="text-end">মূল্য</th>
                                            <th class="text-end">মোট</th>
                                            <th class="text-center" style="width:60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cart->items as $item)
                                            <tr id="cart-item-{{ $item->id }}">
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
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm justify-content-center" style="width:120px;">
                                                        <button class="btn btn-outline-secondary cart-qty-btn" type="button"
                                                                data-action="decrease" data-item-id="{{ $item->id }}"
                                                                {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <input type="number" class="form-control text-center cart-qty-input"
                                                               value="{{ $item->quantity }}" min="1"
                                                               data-item-id="{{ $item->id }}"
                                                               data-max="{{ $item->variant?->maximum_order ?? 999 }}">
                                                        <button class="btn btn-outline-secondary cart-qty-btn" type="button"
                                                                data-action="increase" data-item-id="{{ $item->id }}">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-end">৳{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-end fw-semibold item-line-total" data-item-id="{{ $item->id }}">
                                                    ৳{{ number_format($item->line_total, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-danger cart-remove-btn"
                                                            data-item-id="{{ $item->id }}" title="সরিয়ে ফেলুন">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-arrow-left me-1"></i>কেনাকাটা চালিয়ে যান
                        </a>
                        <button class="btn btn-outline-danger" id="clearCartBtn">
                            <i class="bi bi-trash me-1"></i>কার্ট খালি করুন
                        </button>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">অর্ডার সারসংক্ষেপ</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>পণ্য ({{ $cart->item_count }}টি)</span>
                                <span id="cart-subtotal">৳{{ number_format($cart->subtotal, 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>মোট</strong>
                                <strong class="text-success fs-5" id="cart-total">৳{{ number_format($cart->subtotal, 2) }}</strong>
                            </div>
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                ডেলিভারি ফি চেকআউটের সময় যোগ করা হবে।
                            </div>
                        </div>
                    </div>
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

            document.querySelectorAll('.cart-qty-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const itemId = this.dataset.itemId;
                    const action = this.dataset.action;
                    const input = document.querySelector(`.cart-qty-input[data-item-id="${itemId}"]`);
                    let quantity = parseInt(input.value);

                    if (action === 'increase') {
                        quantity++;
                    } else if (action === 'decrease' && quantity > 1) {
                        quantity--;
                    }

                    updateCartItem(itemId, quantity);
                });
            });

            document.querySelectorAll('.cart-qty-input').forEach(input => {
                input.addEventListener('change', function () {
                    const itemId = this.dataset.itemId;
                    let quantity = parseInt(this.value);
                    if (isNaN(quantity) || quantity < 1) quantity = 1;

                    updateCartItem(itemId, quantity);
                });
            });

            document.querySelectorAll('.cart-remove-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (confirm('আপনি কি এই পণ্যটি কার্ট থেকে সরিয়ে ফেলতে চান?')) {
                        removeCartItem(this.dataset.itemId);
                    }
                });
            });

            document.getElementById('clearCartBtn')?.addEventListener('click', function () {
                if (confirm('আপনি কি পুরো কার্ট খালি করতে চান?')) {
                    clearCart();
                }
            });

            function updateCartItem(itemId, quantity) {
                fetch(`/cart/${itemId}`, {
                    method: 'PUT',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ quantity }),
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`.cart-qty-input[data-item-id="${itemId}"]`).value = data.cart.item.quantity;
                        document.querySelector(`.item-line-total[data-item-id="${itemId}"]`).textContent = '৳' + data.cart.item.line_total;
                        document.getElementById('cart-subtotal').textContent = '৳' + parseFloat(data.cart.subtotal).toFixed(2);
                        document.getElementById('cart-total').textContent = '৳' + parseFloat(data.cart.subtotal).toFixed(2);
                    } else {
                        alert(data.message || 'একটি ত্রুটি ঘটেছে।');
                        location.reload();
                    }
                })
                .catch(() => location.reload());
            }

            function removeCartItem(itemId) {
                fetch(`/cart/${itemId}`, {
                    method: 'DELETE',
                    headers,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`cart-item-${itemId}`);
                        if (row) row.remove();
                        document.getElementById('cart-subtotal').textContent = '৳' + parseFloat(data.cart.subtotal).toFixed(2);
                        document.getElementById('cart-total').textContent = '৳' + parseFloat(data.cart.subtotal).toFixed(2);
                        if (data.cart.item_count === 0) location.reload();
                    } else {
                        alert(data.message || 'একটি ত্রুটি ঘটেছে।');
                    }
                })
                .catch(() => location.reload());
            }

            function clearCart() {
                fetch('/cart/clear', {
                    method: 'DELETE',
                    headers,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                })
                .catch(() => location.reload());
            }
        });
    </script>
    @endpush
</x-layouts.app>
