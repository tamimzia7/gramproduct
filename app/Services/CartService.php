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
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        if (! $sessionId) {
            $sessionId = Session::getId();
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function getCart(?User $user, ?string $sessionId): ?Cart
    {
        if ($user) {
            return Cart::where('user_id', $user->id)->with('items.product', 'items.variant')->first();
        }

        if ($sessionId) {
            return Cart::where('session_id', $sessionId)->with('items.product', 'items.variant')->first();
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
        } else {
            $unitPrice = $product->hasDiscount() ? $product->discount_price : $product->base_price;
        }

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $productVariantId)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity,
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

    public function clearCart(Cart $cart): bool
    {
        return $cart->items()->delete();
    }
}
