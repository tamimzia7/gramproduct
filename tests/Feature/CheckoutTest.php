<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password123')]);
    }

    private function createPurchasableVariant(int $price = 570, int $stock = 10): ProductVariant
    {
        $variant = ProductVariant::factory()->default()->create([
            'name' => '৫ কেজি',
            'price' => $price,
        ]);

        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => $stock,
        ]);

        return $variant;
    }

    private function addToCart(User $user, ProductVariant $variant, int $qty = 1): void
    {
        $cart = app(CartService::class)->getOrCreateCart($user, null);
        app(CartService::class)->addItem($cart, $variant->id, $qty);
    }

    private function createAddress(User $user, array $overrides = []): Address
    {
        return Address::factory()->create(array_merge([
            'user_id' => $user->id,
            'is_default' => true,
        ], $overrides));
    }

    private function validConfirmPayload(Address $address): array
    {
        return [
            'address_id' => $address->id,
            'delivery_method' => 'home_delivery',
            'payment_method' => 'cod',
        ];
    }

    // ===================== Access control =====================

    public function test_authenticated_customer_can_access_checkout(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant, 2);
        $this->createAddress($user);

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertSee(__('checkout.title'))
            ->assertSee(__('checkout.address_section'))
            ->assertSee(__('checkout.delivery_section'))
            ->assertSee(__('checkout.grand_total'));
    }

    public function test_guest_customer_is_redirected_to_login(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), [])->assertRedirect(route('login'));
    }

    public function test_empty_cart_cannot_checkout(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertOk()
            ->assertSee('আপনার কার্ট খালি।')
            ->assertDontSee(__('checkout.confirm'));

        // confirm endpoint-ও প্রত্যাখ্যান করে
        $this->actingAs($user)
            ->post(route('checkout.store'), $this->validConfirmPayload(Address::factory()->create(['user_id' => $user->id])))
            ->assertRedirect();
        $this->assertEquals(0, Order::count());
    }

    // ===================== Cart revalidation =====================

    public function test_inactive_product_is_rejected_at_checkout(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant, 1);
        $address = $this->createAddress($user);

        $variant->product->update(['is_active' => false]);

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('আপনার কার্টে থাকা কিছু পণ্যের তথ্য পরিবর্তিত হয়েছে');

        $this->actingAs($user)->post(route('checkout.store'), $this->validConfirmPayload($address))
            ->assertRedirect();
        $this->assertEquals(0, Order::count());
    }

    public function test_inactive_variant_is_rejected_at_checkout(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant, 1);
        $address = $this->createAddress($user);

        $variant->update(['is_active' => false]);

        $this->actingAs($user)->post(route('checkout.store'), $this->validConfirmPayload($address))
            ->assertRedirect();
        $this->assertEquals(0, Order::count());
    }

    public function test_price_change_is_detected_and_current_price_is_used(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant(price: 570, stock: 10);
        $this->addToCart($user, $variant, 2);   // cart captured ৫৭০
        $address = $this->createAddress($user);

        // মূল্য বাড়ানো হলো — silent proceed নয়; notice banner দেখানো হয়
        $variant->update(['price' => 600]);
        $content = $this->actingAs($user)->get(route('checkout.index'))->getContent();

        $this->assertStringContainsString(__('checkout.errors.price_changed_item', ['name' => $variant->product->name]), $content);

        // confirm — server current price (৬০০×২=১২০০) দিয়েই অর্ডার হয়
        $this->actingAs($user)->post(route('checkout.store'), $this->validConfirmPayload($address))
            ->assertRedirect();

        $order = Order::first();
        $this->assertEquals(1200.0, (float) $order->subtotal);
        $this->assertEquals(1200.0 + config('delivery.fees.home_delivery'), (float) $order->grand_total);
    }

    public function test_inventory_change_is_detected(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant(stock: 10);
        $this->addToCart($user, $variant, 7);
        $address = $this->createAddress($user);

        // স্টক কমে গেছে
        $variant->inventory()->update(['quantity' => 4]);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validConfirmPayload($address));

        $response->assertRedirect();

        // redirect-এর পর checkout পেজেই বাংলা বার্তা (available>0 → cap message)
        $content = $this->actingAs($user)->get(route('checkout.index'))->getContent();

        $this->assertStringContainsString(
            __('checkout.errors.stock_capped', [
                'name' => $variant->product->name,
                'max' => BengaliNumber::format(4),
            ]),
            $content,
        );
        $this->assertEquals(0, Order::count());
    }

    // ===================== Successful order =====================

    public function test_successful_checkout_creates_pending_unpaid_order_and_clears_cart(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant(price: 570, stock: 10);
        $this->addToCart($user, $variant, 2);
        $address = $this->createAddress($user, [
            'name' => 'তামিম',
            'phone' => '01712345678',
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validConfirmPayload($address));

        $response->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(Order::PAYMENT_UNPAID, $order->payment_status);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('home_delivery', $order->delivery_method);

        // server-side হিসাব
        $fee = (float) config('delivery.fees.home_delivery');
        $this->assertEquals(1140.0, (float) $order->subtotal);
        $this->assertEquals($fee, (float) $order->delivery_fee);
        $this->assertEquals(1140.0 + $fee, (float) $order->grand_total);

        // address snapshot
        $this->assertEquals('তামিম', $order->receiver_name);
        $this->assertEquals('01712345678', $order->receiver_phone);

        // item snapshot
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 570,
            'line_total' => 1140,
        ]);

        // stock RESERVED (not sold) — available কমেছে, transaction লেজারে আছে
        $this->assertEquals(8, $variant->fresh()->availableQuantity());
        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'reservation',
            'reference_type' => 'order',
            'reference_id' => $order->id,
        ]);

        // cart খালি
        $this->assertTrue($user->cart()->first()->items()->count() === 0);
    }

    public function test_success_page_is_owner_only(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant);
        $address = $this->createAddress($user);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validConfirmPayload($address));
        $exception = $response->exception;
        dump('status:', $response->status(), 'exception:', $exception ? get_class($exception) : null, $exception?->getMessage());
        $order = Order::first();
        dump('orders:', Order::count());

        $this->actingAs($user)->get(route('checkout.success', $order))->assertOk();
        $this->actingAs($other)->get(route('checkout.success', $order))->assertForbidden();

        // actingAs guard-এ থেকে যায় — সত্যিকারের guest হতে logout করতে হবে
        auth()->guard('web')->logout();
        $this->get(route('checkout.success', $order))->assertRedirect(route('login'));
    }

    // ===================== Server-side total security =====================

    public function test_client_side_totals_are_ignored(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant(price: 570, stock: 10);
        $this->addToCart($user, $variant, 2);
        $address = $this->createAddress($user);

        // manipulation attempt — browser থেকে পাঠানো মূল্য/ফি/total
        $this->actingAs($user)->post(route('checkout.store'), array_merge($this->validConfirmPayload($address), [
            'subtotal' => 1,
            'delivery_fee' => 0,
            'grand_total' => 1,
            'unit_price' => 1,
        ]))->assertRedirect();

        $order = Order::first();
        $fee = (float) config('delivery.fees.home_delivery');
        $this->assertEquals(1140.0, (float) $order->subtotal);
        $this->assertEquals($fee, (float) $order->delivery_fee);
        $this->assertEquals(1140.0 + $fee, (float) $order->grand_total);
    }

    public function test_invalid_delivery_method_rejected(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant);
        $address = $this->createAddress($user);

        $payload = $this->validConfirmPayload($address);
        $payload['delivery_method'] = 'drone_delivery';

        $this->actingAs($user)->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('delivery_method');

        $this->assertEquals(0, Order::count());
    }

    public function test_invalid_payment_method_rejected(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant);
        $address = $this->createAddress($user);

        $payload = $this->validConfirmPayload($address);
        $payload['payment_method'] = 'bkash';

        $this->actingAs($user)->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('payment_method');

        $this->assertEquals(0, Order::count());
    }

    // ===================== Overselling prevention =====================

    public function test_inventory_locking_prevents_overselling_between_customers(): void
    {
        $stock = 10;

        $userA = $this->createUser();
        $userB = $this->createUser();
        $variant = $this->createPurchasableVariant(stock: $stock);
        $addressA = $this->createAddress($userA);
        $addressB = $this->createAddress($userB);

        // দুজনেই স্টক ঘোষণার আগে বেশি করে যোগ করেছে
        $this->addToCart($userA, $variant, 6);
        $this->addToCart($userB, $variant, 5);

        // A confirm — ৬টি reserve, available ৪
        $this->actingAs($userA)->post(route('checkout.store'), $this->validConfirmPayload($addressA))
            ->assertRedirect();
        $this->assertEquals(4, $variant->fresh()->availableQuantity());

        $cappedMessage = __('checkout.errors.stock_capped', ['name' => $variant->product->name, 'max' => BengaliNumber::format(4)]);
        // B confirm — ৫ > ৪ → rejected, overselling অসম্ভব
        $response = $this->actingAs($userB)->post(route('checkout.store'), $this->validConfirmPayload($addressB));
        $response->assertRedirect();

        $content = $this->actingAs($userB)->get(route('checkout.index'))->getContent();
        $this->assertStringContainsString($cappedMessage, $content);

        $this->assertEquals(1, Order::count());
        $this->assertEquals(4, $variant->fresh()->availableQuantity());
    }

    // ===================== Addresses =====================

    public function test_address_can_be_created_with_bengali_validation(): void
    {
        $user = $this->createUser();

        // missing fields → বাংলা messages
        $response = $this->actingAs($user)->post(route('addresses.store'), []);
        $response->assertSessionHasErrors(['name', 'phone', 'division', 'district', 'upazila', 'area', 'address_line']);
        $this->assertStringContainsString('নাম লিখুন।', session('errors')->first('name'));
        $this->assertStringContainsString('বিভাগ নির্বাচন করুন।', session('errors')->first('division'));

        // invalid phone format
        $this->actingAs($user)->post(route('addresses.store'), [
            'name' => 'তামিম',
            'phone' => '12345',
            'division' => 'ঢাকা',
            'district' => 'গাজীপুর',
            'upazila' => 'কালীগঞ্জ',
            'area' => 'বাজার',
            'address_line' => 'রোড ৩',
        ])->assertSessionHasErrors('phone');
        $this->assertStringContainsString('সঠিক মোবাইল নম্বর লিখুন।', session('errors')->first('phone'));

        // district ভিন্ন বিভাগের → rejected
        $this->actingAs($user)->post(route('addresses.store'), [
            'name' => 'তামিম',
            'phone' => '01712345678',
            'division' => 'ঢাকা',
            'district' => 'যশোর',
            'upazila' => 'কালীগঞ্জ',
            'area' => 'বাজার',
            'address_line' => 'রোড ৩',
        ])->assertSessionHasErrors('district');

        // valid → created as default (প্রথম ঠিকানা)
        $this->actingAs($user)->post(route('addresses.store'), [
            'name' => 'তামিম',
            'phone' => '01712345678',
            'division' => 'ঢাকা',
            'district' => 'গাজীপুর',
            'upazila' => 'কালীগঞ্জ',
            'area' => 'উত্তরা',
            'address_line' => 'বাসা ১২, রোড ৩',
            'postal_code' => '1700',
            'delivery_note' => 'বিকেলের পর ডেলিভারি দিলে সুবিধা হবে।',
        ])->assertRedirect();

        $address = Address::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue((bool) $address->is_default);
        $this->assertEquals('গাজীপুর', $address->district);
    }

    public function test_address_can_be_updated(): void
    {
        $user = $this->createUser();
        $address = $this->createAddress($user, ['area' => 'পুরনো এলাকা']);

        $this->actingAs($user)->patch(route('addresses.update', $address), [
            'name' => 'তামিম',
            'phone' => '01812345678',
            'division' => 'ঢাকা',
            'district' => 'গাজীপুর',
            'upazila' => 'কালীগঞ্জ',
            'area' => 'নতুন এলাকা',
            'address_line' => 'বাসা ১২',
        ])->assertRedirect();

        $this->assertEquals('নতুন এলাকা', $address->fresh()->area);
    }

    public function test_address_can_be_deleted_and_default_promotes(): void
    {
        $user = $this->createUser();
        $first = $this->createAddress($user, ['is_default' => true]);
        $second = $this->createAddress($user, ['is_default' => false]);

        $this->actingAs($user)->delete(route('addresses.destroy', $first))->assertRedirect();

        $this->assertDatabaseMissing('addresses', ['id' => $first->id]);
        $this->assertTrue((bool) $second->fresh()->is_default); // default promote
    }

    public function test_default_address_logic_only_one_default(): void
    {
        $user = $this->createUser();
        $first = $this->createAddress($user, ['is_default' => true]);
        $second = $this->createAddress($user, ['is_default' => false]);

        $this->actingAs($user)->patch(route('addresses.default', $second))->assertRedirect();

        $this->assertFalse((bool) $first->fresh()->is_default);
        $this->assertTrue((bool) $second->fresh()->is_default);
        $this->assertEquals(1, Address::where('user_id', $user->id)->where('is_default', true)->count());
    }

    public function test_user_cannot_access_another_users_address(): void
    {
        $owner = $this->createUser();
        $intruder = $this->createUser();
        $address = $this->createAddress($owner);

        $fullPayload = [
            'name' => 'হ্যাকার',
            'phone' => '01712345678',
            'division' => 'ঢাকা',
            'district' => 'গাজীপুর',
            'upazila' => 'কালীগঞ্জ',
            'area' => 'হ্যাক এলাকা',
            'address_line' => 'হ্যাক ঠিকানা',
        ];

        // ownership — service/controller 403 দেয় (validation pass করার পরেও)
        $this->actingAs($intruder)
            ->patch(route('addresses.update', $address), $fullPayload)
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('addresses.destroy', $address))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('addresses.default', $address))
            ->assertForbidden();

        // confirm-এ অন্যের address_id → scoped findOrFail → 404
        $this->actingAs($intruder)
            ->post(route('checkout.store'), $this->validConfirmPayload($address))
            ->assertNotFound();

        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'name' => $address->name]);
    }

    // ===================== Bengali UI audit =====================

    public function test_checkout_page_contains_no_unintended_english_ui_strings(): void
    {
        $user = $this->createUser();
        $variant = $this->createPurchasableVariant();
        $this->addToCart($user, $variant, 2);
        $this->createAddress($user);

        $forbidden = [
            'Checkout Summary', 'Delivery Address', 'Delivery Method', 'Grand Total',
            'Subtotal', 'Place Order', 'Confirm Order', 'Shipping', 'Cash on Delivery',
        ];

        foreach ([route('checkout.index'), route('cart.index')] as $url) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();

            foreach ($forbidden as $needle) {
                $this->assertStringNotContainsString($needle, $response->getContent(), "\"{$needle}\" found on {$url}");
            }
        }
    }
}
