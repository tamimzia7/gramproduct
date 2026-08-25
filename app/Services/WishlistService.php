<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;

class WishlistService
{
    public function getWishlistItems(User $user): Collection
    {
        return WishlistItem::where('user_id', $user->id)
            ->with(['product.category', 'product.variants', 'variant'])
            ->latest()
            ->get();
    }

    public function addItem(User $user, int $productId, ?int $productVariantId = null): WishlistItem
    {
        $product = Product::where('is_active', true)->findOrFail($productId);

        if ($productVariantId) {
            ProductVariant::where('product_id', $productId)
                ->where('id', $productVariantId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $existing = WishlistItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $productVariantId)
            ->first();

        if ($existing) {
            abort(422, 'পণ্যটি ইতোমধ্যে আপনার ইচ্ছেতালিকায় রয়েছে।');
        }

        return WishlistItem::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'product_variant_id' => $productVariantId,
        ]);
    }

    public function removeItem(User $user, WishlistItem $wishlistItem): bool
    {
        if ($wishlistItem->user_id !== $user->id) {
            abort(403, 'আপনার এই ইচ্ছেতালিকা আইটেমে অ্যাক্সেস নেই।');
        }

        return $wishlistItem->delete();
    }

    public function getCount(User $user): int
    {
        return WishlistItem::where('user_id', $user->id)->count();
    }
}
