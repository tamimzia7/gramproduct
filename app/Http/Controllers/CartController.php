<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(Request $request): View
    {
        $cart = $this->cartService->getCart(
            $request->user(),
            $request->session()->getId(),
        );

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId(),
        );

        $cartItem = $this->cartService->addItem(
            $cart,
            $request->integer('product_id'),
            $request->input('product_variant_id') ? $request->integer('product_variant_id') : null,
            $request->integer('quantity', 1),
        );

        $cart->load('items.product', 'items.variant');

        return response()->json([
            'success' => true,
            'message' => 'পণ্যটি কার্টে যোগ করা হয়েছে।',
            'cart' => [
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
                'items' => $cart->items->map(fn (CartItem $item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2),
                    'line_total' => number_format($item->line_total, 2),
                ]),
            ],
        ]);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($cartItem, $request);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = $this->cartService->updateQuantity(
            $cartItem,
            $request->integer('quantity'),
        );

        $cart = $cartItem->cart->load('items.product', 'items.variant');

        return response()->json([
            'success' => true,
            'message' => 'কার্ট আপডেট করা হয়েছে।',
            'cart' => [
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
                'item' => [
                    'id' => $cartItem->id,
                    'line_total' => number_format($cartItem->line_total, 2),
                    'quantity' => $cartItem->quantity,
                ],
            ],
        ]);
    }

    public function remove(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($cartItem, $request);

        $cart = $cartItem->cart;
        $cartItem->delete();
        $cart->load('items.product', 'items.variant');

        return response()->json([
            'success' => true,
            'message' => 'পণ্যটি কার্ট থেকে সরিয়ে ফেলা হয়েছে।',
            'cart' => [
                'item_count' => $cart->item_count,
                'subtotal' => $cart->subtotal,
            ],
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart(
            $request->user(),
            $request->session()->getId(),
        );

        if ($cart) {
            $this->cartService->clearCart($cart);
            $cart->load('items');
        }

        return response()->json([
            'success' => true,
            'message' => 'আপনার কার্ট খালি করা হয়েছে।',
            'cart' => [
                'item_count' => 0,
                'subtotal' => 0,
            ],
        ]);
    }

    private function authorizeCartItem(CartItem $cartItem, Request $request): void
    {
        $cart = $cartItem->cart;

        if ($request->user()) {
            if ($cart->user_id !== $request->user()->id) {
                abort(403, 'আপনার এই কার্ট আইটেমে অ্যাক্সেস নেই।');
            }
        } else {
            if ($cart->session_id !== $request->session()->getId()) {
                abort(403, 'আপনার এই কার্ট আইটেমে অ্যাক্সেস নেই।');
            }
        }
    }
}
