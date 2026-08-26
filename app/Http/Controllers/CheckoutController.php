<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryMethod;
use App\Exceptions\CartException;
use App\Exceptions\CheckoutException;
use App\Models\Inventory;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryService;
use App\Services\OrderCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private DeliveryService $deliveryService,
        private AddressService $addressService,
        private CartService $cartService,
        private OrderCreationService $orderCreationService,
    ) {}

    /**
     * চেকআউট পেজ — কার্ট পুনঃযাচাই + ঠিকানা/ডেলিভারি/সারাংশ
     */
    public function index(): View
    {
        $user = auth()->user();
        $addresses = $user->addresses()->latest()->get();
        $methods = $this->deliveryService->methods();

        try {
            $summary = $this->checkoutService->buildSummary(
                $user,
                $addresses->firstWhere('is_default') ?? $addresses->first(),
                $this->selectedMethod(),
            );
        } catch (CheckoutException $exception) {
            return view('checkout.index', [
                'error' => $exception->getMessage(),
                'issues' => $exception->issues,
                'notices' => [],
                'isEmptyState' => $exception->issues === [] && str_contains($exception->getMessage(), 'খালি'),
                'summary' => null,
                'addresses' => $addresses,
                'methods' => $methods,
                'defaultAddressId' => null,
            ]);
        }

        return view('checkout.index', [
            'summary' => $summary,
            'error' => null,
            'issues' => [],
            'notices' => $summary->notices,
            'isEmptyState' => false,
            'addresses' => $addresses,
            'methods' => $methods,
            'defaultAddressId' => ($addresses->firstWhere('is_default') ?? $addresses->first())?->id,
        ]);
    }

    /**
     * নির্বাচিত ডেলিভারি মেথড — browser value যাচাই করেই
     */
    private function selectedMethod(): DeliveryMethod
    {
        $requested = (string) request('delivery_method', config('delivery.default_method'));

        return $this->deliveryService->isValidMethod($requested)
            ? DeliveryMethod::from($requested)
            : $this->deliveryService->defaultMethod();
    }

    /**
     * অর্ডার নিশ্চিতকরণ — transaction-এ: inventory lock → পুনঃযাচাই →
     * stock reservation → pending order (unpaid) → cart খালি।
     *
     * Phase 09 Order/Payment মডিউল এই সীমানা থেকেই শুরু হবে।
     */
    public function store(): RedirectResponse
    {
        $data = request()->validate([
            'address_id' => ['required', 'integer'],
            'delivery_method' => ['required', 'string'],
            'payment_method' => ['required', 'in:cod'],
        ], [
            'address_id.required' => __('checkout.errors.address_required'),
            'delivery_method.required' => __('checkout.errors.delivery_method'),
            'payment_method.in' => __('checkout.errors.payment_method'),
        ]);

        // ownership — browser-এর address_id কখনো trusted নয়
        $address = auth()->user()->addresses()->findOrFail($data['address_id']);

        if (! $this->deliveryService->isValidMethod($data['delivery_method'])) {
            return back()->withErrors(['delivery_method' => __('checkout.errors.delivery_method')]);
        }

        $method = DeliveryMethod::from($data['delivery_method']);
        $user = auth()->user();
        $cart = $this->cartService->getCart($user, null);

        // খালি কার্ট — transaction-এ ঢোকার আগেই বাংলা error দিয়ে ফেরত
        if (! $cart || $cart->items()->count() === 0) {
            return back()->withErrors(['checkout' => __('checkout.errors.empty_cart')]);
        }

        try {
            $order = DB::transaction(function () use ($user, $address, $method, $cart): Order {
                // ১) inventory rows lock — concurrent overselling প্রতিরোধ
                $variantIds = $cart->items()->pluck('product_variant_id')->all();
                Inventory::query()
                    ->whereIn('product_variant_id', $variantIds)
                    ->lockForUpdate()
                    ->get();

                // ২) lock-এর ভেতরেই fresh পুনঃযাচাই + server-side হিসাব
                $summary = $this->checkoutService->buildSummary($user, $address, $method);

                // ৩) reservation + pending order snapshot
                $order = $this->orderCreationService->createFromCheckout($user, $summary);

                // ৪) cart খালি
                $this->cartService->clearCart($cart);

                return $order;
            });
        } catch (CheckoutException|CartException|InventoryException $exception) {
            return back()
                ->withErrors(['checkout' => $exception->getMessage()])
                ->with('issues', $exception instanceof CheckoutException ? $exception->issues : []);
        } catch (\Throwable) {
            // §27/§33 — কোনো technical error গ্রাহকের কাছে যায় না
            return back()->withErrors(['checkout' => __('cart.messages.generic_error')]);
        }

        return redirect()->route('checkout.success', $order)
            ->with('success', __('checkout.messages.order_placed'));
    }

    /**
     * সফল অর্ডার — ন্যূনতম নিশ্চিতকরণ পেজ (Phase 09 full order page)
     */
    public function success(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load('items');

        return view('checkout.success', compact('order'));
    }
}
