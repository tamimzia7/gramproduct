<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    public function __construct(
        private CartService $cartService,
    ) {}

    /**
     * ইউজারের ইচ্ছেতালিকা — পণ্য-ভিত্তিক, eager-loaded
     */
    public function getWishlistItems(User $user): Collection
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->with([
                'product.category',
                'product.primaryImage',
                'product.images',
                'product.activeVariants.inventory',
            ])
            ->latest()
            ->get();
    }

    /**
     * ইচ্ছেতালিকায় পণ্য যোগ — active product; duplicate unique constraint-এ আটকায়
     */
    public function addItem(User $user, int $productId): WishlistItem
    {
        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($productId);

        $existing = WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(fn () => WishlistItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]));
    }

    public function exists(User $user, int $productId): bool
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
    }

    public function getCount(User $user): int
    {
        return WishlistItem::query()->where('user_id', $user->id)->count();
    }

    /**
     * অপসারণ — শুধুই নিজের item (ownership যাচাই)
     */
    public function removeItem(User $user, WishlistItem $wishlistItem): bool
    {
        abort_unless($wishlistItem->user_id === $user->id, 403, __('cart.errors.not_yours'));

        return (bool) $wishlistItem->delete();
    }

    /**
     * পণ্য-ভিত্তিক অপসারণ (toggle UX) — শুধু নিজের row; না থাকলে নীরব
     */
    public function removeByProduct(User $user, int $productId): void
    {
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * ইচ্ছেতালিকা → কার্ট — পণ্যের active default variant দিয়ে
     *
     * @return array{cart_item: CartItem|null}
     *
     * @throws HttpResponseException|CartException
     */
    public function moveToCart(User $user, WishlistItem $wishlistItem): array
    {
        abort_unless($wishlistItem->user_id === $user->id, 403, __('cart.errors.not_yours'));

        $product = $wishlistItem->product()->with(['activeVariants.inventory'])->first();

        if (! $product || ! $product->isActive() || ! $product->hasActiveVariants()) {
            throw new \App\Exceptions\CartException(__('cart.errors.no_variant'));
        }

        $variant = $product->displayVariant();

        $cart = $this->cartService->getOrCreateCart($user, null);

        $cartItem = $this->cartService->addItem($cart, $variant->getKey(), 1);

        $wishlistItem->delete();

        return ['cart_item' => $cartItem];
    }
}
