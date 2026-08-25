@props(['cart' => null])

@php
    $cart = $cart ?? session()->get('cart');
    $cartItemCount = 0;
    $cartSubtotal = 0;

    if ($cart) {
        $cartItemCount = $cart->item_count;
        $cartSubtotal = $cart->subtotal;
    }
@endphp

<div class="nav-item dropdown" id="mini-cart-dropdown">
    <a class="nav-link position-relative" href="{{ route('cart.index') }}"
       role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-cart3 fs-5"></i>
        @if ($cartItemCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mini-cart-badge"
                  style="font-size:0.65rem;">
                {{ $cartItemCount }}
            </span>
        @endif
    </a>

    @if ($cartItemCount > 0)
        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:320px;">
            <h6 class="dropdown-header">কার্ট ({{ $cartItemCount }}টি পণ্য)</h6>
            <div class="mini-cart-items" style="max-height:300px;overflow-y:auto;">
                @foreach ($cart->items->take(5) as $item)
                    <div class="d-flex align-items-center py-2 border-bottom">
                        @if ($item->product->image)
                            <img src="{{ Storage::url($item->product->image) }}"
                                 alt="{{ $item->product->name }}"
                                 class="rounded me-2"
                                 style="width:40px;height:40px;object-fit:cover;">
                        @else
                            <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center me-2"
                                 style="width:40px;height:40px;">
                                <i class="bi bi-box-seam text-success small"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 small">
                            <div class="fw-semibold">{{ $item->product->name }}</div>
                            @if ($item->variant)
                                <div class="text-muted">{{ $item->variant->name }}</div>
                            @endif
                            <div>{{ $item->quantity }} × ৳{{ number_format($item->unit_price, 0) }}</div>
                        </div>
                        <div class="fw-semibold small">৳{{ number_format($item->line_total, 0) }}</div>
                    </div>
                @endforeach
                @if ($cartItemCount > 5)
                    <div class="text-center py-2 small text-muted">
                        আরও {{ $cartItemCount - 5 }}টি পণ্য...
                    </div>
                @endif
            </div>
            <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                <span class="fw-semibold">মোট:</span>
                <span class="fw-bold text-success">৳{{ number_format($cartSubtotal, 0) }}</span>
            </div>
            <div class="d-grid gap-2 mt-2">
                <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-success">কার্ট দেখুন</a>
            </div>
        </div>
    @else
        <div class="dropdown-menu dropdown-menu-end p-3 text-center" style="min-width:250px;">
            <i class="bi bi-cart-x fs-3 text-muted"></i>
            <p class="mt-2 mb-0 small text-muted">আপনার কার্ট খালি।</p>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-success mt-2">কেনাকাটা শুরু করুন</a>
        </div>
    @endif
</div>
