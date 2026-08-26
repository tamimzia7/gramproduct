<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService,
        private CartService $cartService,
    ) {}

    /**
     * আমার ইচ্ছেতালিকা
     */
    public function index(Request $request): View
    {
        $wishlistItems = $this->wishlistService->getWishlistItems($request->user());

        return view('wishlist.index', [
            'wishlistItems' => $wishlistItems,
            'savedProductIds' => $wishlistItems->pluck('product_id')->all(),
        ]);
    }

    /**
     * ইচ্ছেতালিকায় যোগ — লগইন প্রয়োজন; guest বাংলা নির্দেশনা পায়
     */
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => __('cart.wishlist.login_required'),
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $this->wishlistService->addItem($request->user(), (int) $validated['product_id']);

        return response()->json([
            'success' => true,
            'message' => __('cart.wishlist.added'),
            'wishlist_count' => $this->wishlistService->getCount($request->user()),
            'saved' => true,
        ]);
    }

    /**
     * পণ্য-ভিত্তিক অপসারণ — toggle UX-এর জন্য (নিজের item-ই শুধু ফেলে)
     */
    public function destroyByProduct(Request $request, Product $product): JsonResponse
    {
        if (! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => __('cart.wishlist.login_required'),
            ], 401);
        }

        $this->wishlistService->removeByProduct($request->user(), $product->id);

        return response()->json([
            'success' => true,
            'message' => __('cart.wishlist.removed'),
            'wishlist_count' => $this->wishlistService->getCount($request->user()),
            'saved' => false,
        ]);
    }

    /**
     * ইচ্ছেতালিকা থেকে সরানো — শুধু নিজের item
     */
    public function destroy(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        $this->wishlistService->removeItem($request->user(), $wishlistItem);

        return response()->json([
            'success' => true,
            'message' => __('cart.wishlist.removed'),
            'wishlist_count' => $this->wishlistService->getCount($request->user()),
            'saved' => false,
        ]);
    }

    /**
     * ইচ্ছেতালিকা → কার্ট — ডিফল্ট active variant দিয়ে
     */
    public function moveToCart(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        try {
            $this->wishlistService->moveToCart($request->user(), $wishlistItem);
        } catch (CartException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('cart.wishlist.moved_to_cart'),
            'wishlist_count' => $this->wishlistService->getCount($request->user()),
            'cart_count' => $this->cartService->getItemCount($request->user(), $request->session()->getId()),
        ]);
    }
}
