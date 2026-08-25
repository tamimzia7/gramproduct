<?php

namespace App\Http\Controllers;

use App\Models\WishlistItem;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService,
        private CartService $cartService,
    ) {}

    public function index(Request $request): View
    {
        $wishlistItems = $this->wishlistService->getWishlistItems($request->user());

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $wishlistItem = $this->wishlistService->addItem(
            $request->user(),
            $request->integer('product_id'),
            $request->input('product_variant_id') ? $request->integer('product_variant_id') : null,
        );

        $count = $this->wishlistService->getCount($request->user());

        return response()->json([
            'success' => true,
            'message' => 'পণ্যটি আপনার ইচ্ছেতালিকায় যোগ করা হয়েছে।',
            'wishlist_count' => $count,
        ]);
    }

    public function destroy(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        $this->wishlistService->removeItem($request->user(), $wishlistItem);

        $count = $this->wishlistService->getCount($request->user());

        return response()->json([
            'success' => true,
            'message' => 'পণ্যটি ইচ্ছেতালিকা থেকে সরানো হয়েছে।',
            'wishlist_count' => $count,
        ]);
    }

    public function moveToCart(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        if ($wishlistItem->user_id !== $request->user()->id) {
            abort(403, 'আপনার এই ইচ্ছেতালিকা আইটেমে অ্যাক্সেস নেই।');
        }

        $product = $wishlistItem->product;

        if (! $product || ! $product->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'পণ্যটি বর্তমানে উপলব্ধ নয়।',
            ], 422);
        }

        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
        );

        $this->cartService->addItem(
            $cart,
            $wishlistItem->product_id,
            $wishlistItem->product_variant_id,
            1,
        );

        $wishlistItem->delete();

        $cart->load('items.product', 'items.variant');

        return response()->json([
            'success' => true,
            'message' => 'পণ্যটি কার্টে যোগ করা হয়েছে।',
            'wishlist_count' => $this->wishlistService->getCount($request->user()),
            'cart' => [
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
            ],
        ]);
    }
}
