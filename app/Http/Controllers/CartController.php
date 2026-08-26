<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Exceptions\InventoryException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    /**
     * আমার কার্ট — items eager-loaded (product/variant/inventory)
     */
    public function index(Request $request): View
    {
        $cart = $this->currentCart($request);
        $cart->load([
            'items.variant.product.primaryImage',
            'items.variant.product.images',
            'items.variant.inventory',
        ]);

        // cart-মূল্য বনাম বর্তমান মূল্যের অমিল — UI সতর্কবার্তা
        $priceChanges = $this->cartService->priceChanges($cart);

        return view('cart.index', [
            'cart' => $cart,
            'priceChanges' => collect($priceChanges)->keyBy(fn ($change) => $change['item']->id),
        ]);
    }

    /**
     * কার্টে যোগ — guest/auth উভয়ই; সব তথ্য DB থেকে
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ], [
            'product_variant_id.required' => __('cart.errors.unavailable'),
            'quantity.min' => __('cart.errors.quantity_minimum'),
        ]);

        try {
            $cart = $this->currentCart($request);

            $this->cartService->addItem(
                $cart,
                (int) $validated['product_variant_id'],
                (int) ($validated['quantity'] ?? 1),
            );
        } catch (CartException|InventoryException $exception) {
            return $this->errorResponse($request, $exception->getMessage());
        }

        if (! $request->expectsJson()) {
            return redirect()->route('cart.index')->with('success', __('cart.messages.added'));
        }

        return response()->json([
            'success' => true,
            'message' => __('cart.messages.added'),
            'cart_count' => $this->cartService->getItemCount($request->user(), $request->session()->getId()),
            'subtotal' => (float) ($this->cartService->getCart($request->user(), $request->session()->getId())?->subtotal ?? 0),
        ]);
    }

    /**
     * পরিমাণ হালনাগাদ — শুধু নিজের cart item
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ], [
            'quantity.min' => __('cart.errors.quantity_minimum'),
            'quantity.required' => __('cart.errors.quantity_minimum'),
        ]);

        try {
            $this->cartService->updateQuantity($cartItem, (int) $validated['quantity']);
        } catch (CartException|InventoryException $exception) {
            return $this->errorResponse($request, $exception->getMessage());
        }

        if (! $request->expectsJson()) {
            return redirect()->route('cart.index')->with('success', __('cart.messages.updated'));
        }

        return response()->json([
            'success' => true,
            'message' => __('cart.messages.updated'),
            'line_total' => (float) $cartItem->fresh()->line_total,
            'cart_count' => $this->cartService->getItemCount($request->user(), $request->session()->getId()),
            'subtotal' => (float) ($this->cartService->getCart($request->user(), $request->session()->getId())?->subtotal ?? 0),
        ]);
    }

    /**
     * Item অপসারণ
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);

        $cart = $this->currentCart($request);
        $this->cartService->removeItem($cartItem, $cart);

        if (! $request->expectsJson()) {
            return redirect()->route('cart.index')->with('success', __('cart.messages.removed'));
        }

        return response()->json([
            'success' => true,
            'message' => __('cart.messages.removed'),
            'cart_count' => $this->cartService->getItemCount($request->user(), $request->session()->getId()),
        ]);
    }

    /**
     * পুরো কার্ট খালি
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cartService->clearCart($this->currentCart($request));

        if (! $request->expectsJson()) {
            return redirect()->route('cart.index')->with('success', __('cart.messages.cleared'));
        }

        return response()->json(['success' => true, 'message' => __('cart.messages.cleared'), 'cart_count' => 0]);
    }

    // ---------- helpers ----------

    private function currentCart(Request $request): Cart
    {
        return $this->cartService->getOrCreateCart($request->user(), $request->session()->getId());
    }

    private function authorizeItem(Request $request, CartItem $cartItem): void
    {
        $cart = $this->cartService->getCart($request->user(), $request->session()->getId());

        abort_unless($cart !== null && $cartItem->cart_id === $cart->id, 403, __('cart.errors.not_yours'));
    }

    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return redirect()
            ->back()
            ->withErrors(['cart' => $message]);
    }
}
