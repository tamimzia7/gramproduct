<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getOrCreateCart(?User $user, ?string $sessionId): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
            );
        }

        if (! $sessionId) {
            $sessionId = Session::getId();
        }

        return Cart::firstOrCreate(
            ['session_id' => $sessionId],
        );
    }

    public function getCart(?User $user, ?string $sessionId): ?Cart
    {
        if ($user) {
            return Cart::where('user_id', $user->id)->with('items.product')->with('items.variant')->first();
        }

        if ($sessionId) {
            return Cart::where('session_id', $sessionId)->with('items.product')->with('items.variant')->first();
        }

        return null;
    }

    public function addItem(Cart $cart, int $productId, ?int $productVariantId, int $quantity): CartItem
    {
        $product = Product::where('is_active', true)->findOrFail($productId);

        if ($productVariantId) {
            $variant = ProductVariant::where('product_id', $productId)
                ->where('id', $productVariantId)
                ->where('is_active', true)
                ->firstOrFail();
            $unitPrice = $variant->hasDiscount() ? $variant->discount_price : $variant->price;
            $this->validateStock($product, $variant, $quantity);
            $this->validateQuantityLimits($variant, $quantity);
        } else {
            $unitPrice = $product->hasDiscount() ? $product->discount_price : $product->base_price;
            $this->validateStock($product, null, $quantity);
        }

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $productVariantId)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($productVariantId) {
                $this->validateStock($product, $variant ?? null, $newQuantity);
            } else {
                $this->validateStock($product, null, $newQuantity);
            }
            $existingItem->update([
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
            ]);

            return $existingItem;
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'product_variant_id' => $productVariantId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $product = $cartItem->product;
        $variant = $cartItem->variant;

        $this->validateStock($product, $variant, $quantity);

        if ($variant) {
            $this->validateQuantityLimits($variant, $quantity);
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem;
    }

    public function removeItem(CartItem $cartItem): bool
    {
        return $cartItem->delete();
    }

    public function clearCart(Cart $cart): bool
    {
        return $cart->items()->delete();
    }

    public function transferGuestCartToUser(Cart $guestCart, User $user): Cart
    {
        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $guestItem) {
            $existingItem = CartItem::where('cart_id', $userCart->id)
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity,
                ]);
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();

        return $userCart;
    }

    private function validateStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $inventory = Inventory::where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if (! $inventory) {
            return;
        }

        if (! $inventory->isInStock()) {
            abort(422, 'দুঃখিত, পণ্যটি বর্তমানে স্টকে নেই।');
        }

        if ($inventory->available_quantity < $quantity) {
            abort(422, 'দুঃখিত, চাহিদাকৃত পরিমাণে পণ্যটি বর্তমানে উপলব্ধ নেই।');
        }
    }

    private function validateQuantityLimits(ProductVariant $variant, int $quantity): void
    {
        if ($variant->minimum_order > 0 && $quantity < $variant->minimum_order) {
            abort(422, "সর্বনিম্ন অর্ডার পরিমাণ {$variant->minimum_order} একক।");
        }

        if ($variant->maximum_order > 0 && $quantity > $variant->maximum_order) {
            abort(422, "সর্বোচ্চ অর্ডার পরিমাণ {$variant->maximum_order} একক।");
        }
    }
}
