<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createPurchasableVariant(int $price = 120, int $stock = 10): ProductVariant
    {
        $variant = ProductVariant::factory()->default()->create(['price' => $price]);

        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => $stock,
        ]);

        return $variant;
    }

    private function addPayload(ProductVariant $variant, int $quantity = 1): array
    {
        return [
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
        ];
    }

    // ===================== Guest & authenticated add =====================

    public function test_guest_can_add_product_variant_to_cart(): void
    {
        $variant = $this->createPurchasableVariant();

        $response = $this->post(route('cart.store'), $this->addPayload($variant));
        $response->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('carts', ['user_id' => null]);
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            // মূল্য DB থেকেই capture হয়
            'unit_price' => 120,
        ]);
    }

    public function test_authenticated_user_can_add_product_variant(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant(price: 570);

        $this->actingAs($user)->postJson(route('cart.store'), $this->addPayload($variant))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('cart.messages.added'),
                'cart_count' => 1,
            ]);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'unit_price' => 570,
        ]);
    }

    // ===================== Duplicate / distinct variants =====================

    public function test_same_variant_increases_quantity_not_new_row(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant(stock: 50);

        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant, 2));
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant, 1));

        $this->assertEquals(1, CartItem::where('product_variant_id', $variant->id)->count());
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_different_variants_remain_separate_items(): void
    {
        $user = User::factory()->create();
        $oneKg = $this->createPurchasableVariant(price: 120);
        $fiveKg = ProductVariant::factory()->create([
            'product_id' => $oneKg->product_id,
            'name' => '৫ কেজি',
            'sku' => 'SEP-5KG',
            'price' => 570,
        ]);
        Inventory::create(['product_variant_id' => $fiveKg->id, 'quantity' => 20]);

        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($oneKg, 2));
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($fiveKg, 1));

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertCount(2, $cart->items);
    }

    // ===================== Stock guards =====================

    public function test_quantity_cannot_exceed_available_stock(): void
    {
        $variant = $this->createPurchasableVariant(stock: 5);

        $response = $this->post(route('cart.store'), $this->addPayload($variant, 8));

        $response->assertSessionHasErrors();
        $this->assertStringContainsString(
            'সর্বোচ্চ ৫টি যোগ করা যাবে',
            session('errors')->first('cart') ?? '',
        );
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_quantity_increase_is_capped_by_stock(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant(stock: 5);

        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant, 4));
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant, 4))
            ->assertSessionHasErrors();

        // আংশিক প্রথম যোগটাই থাকবে; দ্বিতীয়টি rejected
        $this->assertDatabaseHas('cart_items', ['quantity' => 4]);
    }

    public function test_out_of_stock_variant_cannot_be_added(): void
    {
        $variant = $this->createPurchasableVariant(stock: 0);

        Inventory::where('product_variant_id', $variant->id)->update(['quantity' => 0]);
        $variant->refresh();

        $this->postJson(route('cart.store'), $this->addPayload($variant))
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('cart.errors.out_of_stock'),
            ]);
    }

    public function test_inactive_variant_or_product_cannot_be_added(): void
    {
        $inactiveVariant = ProductVariant::factory()->inactive()->create();
        $inactiveProduct = Product::factory()->inactive()->create();
        $variantOfInactive = ProductVariant::factory()->create(['product_id' => $inactiveProduct->id]);

        foreach ([$inactiveVariant, $variantOfInactive] as $variant) {
            $this->postJson(route('cart.store'), $this->addPayload($variant))
                ->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => __('cart.errors.unavailable'),
                ]);
        }

        $this->assertDatabaseCount('cart_items', 0);
    }

    // ===================== Totals =====================

    public function test_cart_totals_calculate_correctly(): void
    {
        $user = User::factory()->create();
        $oneKg = $this->createPurchasableVariant(price: 120, stock: 30);
        $fiveKg = ProductVariant::factory()->default()->create([
            'product_id' => $oneKg->product_id,
            'sku' => 'TOT-5KG',
            'price' => 570,
        ]);
        Inventory::create(['product_variant_id' => $fiveKg->id, 'quantity' => 30]);

        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($oneKg, 2));   // ২৪০
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($fiveKg, 3)); // ১৭১০

        $cart = Cart::where('user_id', $user->id)->with('items')->first();

        $this->assertEquals(1950.0, $cart->subtotal);   // ২৪০ + ১৭১০
        $this->assertEquals(5, $cart->item_count);      // ২ + ৩
    }

    // ===================== Update / remove / empty =====================

    public function test_cart_item_quantity_can_be_updated(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant(stock: 20);
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant));

        $item = CartItem::where('product_variant_id', $variant->id)->first();

        $this->actingAs($user)->patchJson(route('cart.update', $item), ['quantity' => 3])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_update_beyond_stock_is_rejected_with_bengali_message(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant(stock: 5);
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant));

        $item = CartItem::where('product_variant_id', $variant->id)->first();

        $response = $this->actingAs($user)->patchJson(route('cart.update', $item), ['quantity' => 9]);

        $response->assertStatus(422);
        $message = $response->json('message');
        $this->assertStringContainsString('এই পরিমাণ পণ্য বর্তমানে স্টকে নেই। সর্বোচ্চ', $message);
        $this->assertStringContainsString(BengaliNumber::format(5), $message);
    }

    public function test_cart_item_can_be_removed(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant();
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant));

        $item = CartItem::where('product_variant_id', $variant->id)->first();

        $this->actingAs($user)->deleteJson(route('cart.destroy', $item))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => __('cart.messages.removed')]);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_empty_cart_page_shows_bengali_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cart.index'))
            ->assertOk()
            ->assertSee(__('cart.cart.title'))
            ->assertSee(__('cart.cart.empty_title'))
            ->assertSee('পণ্য দেখুন');
    }

    // ===================== Merge on login =====================

    public function test_guest_cart_merges_into_user_cart(): void
    {
        $service = app(CartService::class);
        $guestSessionId = 'test-guest-session-001';

        // ১) guest session cart — একই variant দুইবার + ভিন্ন variant
        $guestCart = $service->getOrCreateCart(null, $guestSessionId);
        $variantA = $this->createPurchasableVariant(price: 120, stock: 30);
        $variantB = ProductVariant::factory()->default()->create([
            'product_id' => $variantA->product_id,
            'sku' => 'MRG-B',
            'price' => 570,
        ]);
        Inventory::create(['product_variant_id' => $variantB->id, 'quantity' => 10]);

        $service->addItem($guestCart, $variantA->id, 2);
        $service->addItem($guestCart, $variantA->id, 1); // duplicate → ৩
        $service->addItem($guestCart, $variantB->id, 1);

        $user = User::factory()->create();

        // ২) login-এর সময় LoginController এই merge কল করে (pre-attempt session id সহ)
        $service->mergeGuestCart($guestSessionId, $user);

        // ৩) user cart-এ merged items; guest cart মুছে গেছে
        $userCart = Cart::where('user_id', $user->id)->with('items')->first();

        $this->assertNotNull($userCart);
        $this->assertDatabaseMissing('carts', ['session_id' => $guestSessionId]);

        $mergedA = $userCart->items->firstWhere('product_variant_id', $variantA->id);
        $mergedB = $userCart->items->firstWhere('product_variant_id', $variantB->id);

        $this->assertEquals(3, $mergedA->quantity);       // same variant → quantity merge
        $this->assertEquals(1, $mergedB->quantity);       // different variant → separate
        $this->assertCount(2, $userCart->items);

        // merged item-এর মূল্য current variant price থেকেই আসে
        $this->assertEquals(570, (float) $mergedB->unit_price);
    }

    public function test_merge_caps_quantity_at_available_stock_and_skips_invalid_items(): void
    {
        $service = app(CartService::class);
        $guestSessionId = 'test-guest-session-002';

        $guestCart = $service->getOrCreateCart(null, $guestSessionId);

        $valid = $this->createPurchasableVariant(stock: 4, price: 100);
        $invalidVariant = ProductVariant::factory()->inactive()->create();

        // স্টক-হ্রাসের আগে যোগ হওয়া item simulate — সরাসরি row (service cap পাশ করে)
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_variant_id' => $valid->id,
            'quantity' => 10,
            'unit_price' => $valid->price,
        ]);
        // inactive variant-এর item-টিও legacy হিসেবে seed
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_variant_id' => $invalidVariant->id,
            'quantity' => 2,
            'unit_price' => $invalidVariant->price,
        ]);

        $user = User::factory()->create();
        $service->mergeGuestCart($guestSessionId, $user);

        $userCart = Cart::where('user_id', $user->id)->with('items')->first();

        // invalid variant skip হয়েছে
        $this->assertNull($userCart->items->firstWhere('product_variant_id', $invalidVariant->id));

        // valid variant stock-cap (৪) পর্যন্ত সীমিত
        $merged = $userCart->items->firstWhere('product_variant_id', $valid->id);
        $this->assertNotNull($merged);
        $this->assertEquals(4, $merged->quantity);
    }

    // ===================== Ownership security =====================

    public function test_user_cannot_modify_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $variant = $this->createPurchasableVariant(stock: 30);

        $ownerCart = app(CartService::class)->getOrCreateCart($owner, null);
        $item = CartItem::create([
            'cart_id' => $ownerCart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => $variant->price,
        ]);

        $this->actingAs($intruder)->patchJson(route('cart.update', $item), ['quantity' => 5])
            ->assertForbidden();

        $this->actingAs($intruder)->deleteJson(route('cart.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    // ===================== Price revalidation =====================

    public function test_price_change_is_detected_on_cart_page(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant(price: 120, stock: 30);
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant));

        // পরে মূল্য বাড়ানো হলো
        $variant->update(['price' => 130]);

        $content = $this->actingAs($user)->get(route('cart.index'))->getContent();

        $this->assertStringContainsString(__('cart.cart.price_changed'), $content);
        $this->assertStringContainsString(BengaliNumber::money(130), $content);
    }

    // ===================== Inactive product handling =====================

    public function test_inactive_product_item_stays_but_marked_unavailable(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant();
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant));

        $variant->product->update(['is_active' => false]);

        $content = $this->actingAs($user)->get(route('cart.index'))->getContent();

        // data নষ্ট না করেই invalid চিহ্নিত হয়
        $this->assertDatabaseHas('cart_items', ['product_variant_id' => $variant->id]);
        $this->assertStringContainsString('এই পণ্যটি বর্তমানে পাওয়া যাচ্ছে না।', $content);
    }

    // ===================== Bengali UI audit =====================

    public function test_cart_pages_contain_no_unintended_english_ui_strings(): void
    {
        $user = User::factory()->create();
        $variant = $this->createPurchasableVariant();
        $this->actingAs($user)->post(route('cart.store'), $this->addPayload($variant));

        $forbidden = [
            'Your Cart', 'Subtotal', 'Total:', 'Checkout', 'Remove', 'Quantity',
            'Continue Shopping', 'Empty cart',
        ];

        foreach ([route('cart.index'), route('products.show', $variant->product)] as $url) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();

            foreach ($forbidden as $needle) {
                $this->assertStringNotContainsString($needle, $response->getContent(), "\"{$needle}\" found on {$url}");
            }
        }
    }
}
