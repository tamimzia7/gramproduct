<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Exceptions\InventoryException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\BengaliNumber;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    // ---------- Cart lifecycle ----------

    /**
     * বর্তমান কার্ট — লগইন থাকলে user cart, নইলে session cart (না থাকলে null)
     */
    public function getCart(?User $user, ?string $sessionId): ?Cart
    {
        if ($user) {
            return Cart::where('user_id', $user->id)->first();
        }

        if ($sessionId) {
            return Cart::whereNull('user_id')->where('session_id', $sessionId)->first();
        }

        return null;
    }

    /**
     * কার্ট না থাকলে তৈরি করে দেয়
     */
    public function getOrCreateCart(?User $user, ?string $sessionId): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id], ['currency' => 'BDT']);
        }

        $sessionId ??= session()->getId();

        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null],
            ['currency' => 'BDT'],
        );
    }

    /**
     * হেডার ব্যাজের item count — একটি aggregate কুয়েরি
     */
    public function getItemCount(?User $user, ?string $sessionId): int
    {
        $cart = $this->getCart($user, $sessionId);

        return $cart?->items()->sum('quantity') ?? 0;
    }

    // ---------- Items ----------

    /**
     * কার্টে ভ্যারিয়েন্ট যোগ — সমস্ত যাচাই DB থেকে; duplicate হলে quantity বাড়ে
     *
     * @throws CartException|InventoryException
     */
    public function addItem(Cart $cart, int $productVariantId, int $quantity = 1): CartItem
    {
        if ($quantity < 1) {
            throw new CartException(__('cart.errors.quantity_minimum'));
        }

        return DB::transaction(function () use ($cart, $productVariantId, $quantity): CartItem {
            /** @var ProductVariant|null $variant */
            $variant = ProductVariant::query()
                ->with(['product', 'inventory'])
                ->find($productVariantId);

            if (! $variant || ! $variant->isActive() || ! ($variant->product?->isActive())) {
                throw new CartException(__('cart.errors.unavailable'));
            }

            if (! $variant->isPurchasable()) {
                throw new CartException(__('cart.errors.out_of_stock'));
            }

            $existing = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->id)
                ->first();

            $requested = ($existing?->quantity ?? 0) + $quantity;

            // স্টক-সীমা: backorder না থাকলে available-এর মধ্যেই সীমাবদ্ধ
            $available = $this->inventoryService->availableQuantity($variant);
            if (! ($variant->inventory?->allow_backorder ?? false) && $requested > $available) {
                throw new CartException(__('cart.errors.quantity_exceeds_stock', [
                    'max' => BengaliNumber::format(max(
                        0,
                        $available - ($existing?->quantity ?? 0),
                    )),
                ]));
            }

            if ($existing) {
                $existing->update([
                    'quantity' => $requested,
                    // পুনঃযোগে সর্বশেষ মূল্য capture হয়
                    'unit_price' => $variant->price,
                ]);

                return $existing;
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
                'quantity' => $requested,
                'unit_price' => $variant->price,
            ]);
        });
    }

    /**
     * পরিমাণ হালনাগাদ — ownership যাচাই + সর্বশেষ স্টক/মূল্য দিয়ে
     *
     * @throws CartException
     */
    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity < 1) {
            throw new CartException(__('cart.errors.quantity_minimum'));
        }

        return DB::transaction(function () use ($item, $quantity): CartItem {
            $variant = $item->variant()->with(['product', 'inventory'])->firstOrFail();

            if (! $variant->isActive() || ! $variant->product?->isActive()) {
                throw new CartException(__('cart.errors.unavailable'));
            }

            if (! ($variant->inventory?->allow_backorder ?? false)) {
                $available = $this->inventoryService->availableQuantity($variant);

                if ($quantity > $available) {
                    throw new CartException(__('cart.errors.stock_limit', [
                        'max' => BengaliNumber::format($available),
                    ]));
                }
            }

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $variant->price,
            ]);

            return $item;
        });
    }

    /**
     * Item অপসারণ — শুধু মালিকানাধীন cart থেকে
     */
    public function removeItem(CartItem $item, Cart $cart): void
    {
        abort_unless($item->cart_id === $cart->id, 403, __('cart.errors.not_yours'));

        $item->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * মূল্য-পুনঃযাচাই — cart-এ সংরক্ষিত unit_price বনাম বর্তমান variant price
     *
     * @return array<int, array{item: CartItem, old_price: string, new_price: string}>
     */
    public function priceChanges(Cart $cart): array
    {
        return $cart->items
            ->filter(fn (CartItem $item) => (float) $item->unit_price !== (float) ($item->variant?->price))
            ->map(fn (CartItem $item) => [
                'item' => $item,
                'old_price' => (string) $item->unit_price,
                'new_price' => (string) ($item->variant->price ?? '0'),
            ])
            ->values()
            ->all();
    }

    /**
     * Guest cart → user cart merge (login-এর সময়) — transaction-এ:
     * - একই variant → quantity একত্র (stock-cap সহ)
     * - ভিন্ন variant → আলাদা row
     * - inactive/unavailable item skip (guest cart-এর সাথেই বাদ যায়)
     */
    public function mergeGuestCart(string $guestSessionId, User $user): void
    {
        DB::transaction(function () use ($guestSessionId, $user): void {
            $guestCart = Cart::query()
                ->whereNull('user_id')
                ->where('session_id', $guestSessionId)
                ->lockForUpdate()
                ->first();

            if (! $guestCart) {
                return;
            }

            $userCart = $this->getOrCreateCart($user, null);

            foreach ($guestCart->items()->with(['variant.product', 'variant.inventory'])->get() as $guestItem) {
                $variant = $guestItem->variant;

                // activity revalidation — invalid item বাদ
                if (! $variant || ! $variant->isActive() || ! $variant->product?->isActive()) {
                    continue;
                }

                $existing = CartItem::query()
                    ->where('cart_id', $userCart->id)
                    ->where('product_variant_id', $variant->id)
                    ->first();

                $mergedQuantity = ($existing?->quantity ?? 0) + $guestItem->quantity;

                // stock revalidation — cap ছাড়িয়ে গেলে available-এ নামিয়ে আনা
                if (! ($variant->inventory?->allow_backorder ?? false)) {
                    $available = max(1, $this->inventoryService->availableQuantity($variant));
                    $mergedQuantity = min($mergedQuantity, $available);
                }

                if ($existing) {
                    $existing->update(['quantity' => $mergedQuantity]);
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $mergedQuantity,
                        'unit_price' => $variant->price,
                    ]);
                }
            }

            $guestCart->delete();
        });
    }
}
