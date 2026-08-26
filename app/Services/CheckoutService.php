<?php

namespace App\Services;

use App\Data\CheckoutSummary;
use App\Enums\DeliveryMethod;
use App\Exceptions\CheckoutException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\User;
use App\Support\BengaliNumber;

class CheckoutService
{
    public function __construct(
        private DeliveryService $deliveryService,
        private InventoryService $inventoryService,
    ) {}

    /**
     * চেকআউট summary তৈরি — সব যাচাই server-side; কোনো browser-ডেটা trusted নয়।
     *
     * - product/variant active কি না
     * - cart unit_price বনাম current price (অমিল থাকলে current price-ই authoritative)
     * - available stock-এর মধ্যে quantity আছে কি না (backorder ছাড়া)
     *
     *
     * @throws CheckoutException (issues সহ — controller বাংলা warning দেখাবে)
     */
    public function buildSummary(User $user, ?Address $address, DeliveryMethod $deliveryMethod): CheckoutSummary
    {
        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->with([
                'items.variant.product.primaryImage',
                'items.variant.inventory',
            ])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw new CheckoutException(__('checkout.errors.empty_cart'));
        }

        $items = [];
        $issues = [];
        $subtotal = 0.0;

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            $product = $variant?->product;

            // ---------- validity ----------
            if (! $variant || ! $variant->isActive() || ! $product?->isActive()) {
                $issues[] = [
                    'type' => 'unavailable',
                    'message' => __('checkout.errors.item_unavailable', ['name' => $item->variant?->product?->name ?? '—']),
                ];

                continue;
            }

            // ---------- inventory ----------
            $available = $this->inventoryService->availableQuantity($variant);
            $backorder = $variant->inventory?->allow_backorder ?? false;

            if (! $backorder && $available < $item->quantity) {
                $issues[] = [
                    'type' => 'stock',
                    'message' => $available > 0
                        ? __('checkout.errors.stock_capped', [
                            'name' => $product->name,
                            'max' => BengaliNumber::format($available),
                        ])
                        : __('checkout.errors.stock_out', ['name' => $product->name]),
                ];

                continue;
            }

            // ---------- price (current variant price authoritative) ----------
            $unitPrice = (float) $variant->price;
            $lineTotal = round($unitPrice * $item->quantity, 2);
            $subtotal += $lineTotal;

            $items[] = [
                'variant' => $variant,
                'product' => $product,
                'quantity' => $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        if ($issues !== []) {
            throw new CheckoutException(__('checkout.errors.cart_changed'), $issues);
        }

        $subtotal = round($subtotal, 2);
        $fee = $this->deliveryService->fee($deliveryMethod, $user);

        // মূল্য-পরিবর্তন নোটিশ (non-blocking) — current price দিয়েই server হিসাব করে,
        // কিন্তু গ্রাহককে সচেতনভাবে দেখানো হয়
        $notices = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            if ($variant && (float) $item->unit_price !== (float) $variant->price) {
                $notices[] = __('checkout.errors.price_changed_item', [
                    'name' => $variant->product?->name ?? '—',
                ]);
            }
        }

        return new CheckoutSummary(
            items: $items,
            subtotal: $subtotal,
            deliveryFee: $fee,
            grandTotal: round($subtotal + $fee, 2),
            deliveryMethod: $deliveryMethod,
            address: $address,
            notices: $notices,
        );
    }
}
