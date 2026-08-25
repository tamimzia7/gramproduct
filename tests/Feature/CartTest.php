<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'is_active' => true,
            'base_price' => 100.00,
        ], $overrides));
    }

    private function createActiveVariant(Product $product, array $overrides = []): ProductVariant
    {
        return ProductVariant::factory()->create(array_merge([
            'product_id' => $product->id,
            'is_active' => true,
            'price' => 150.00,
            'minimum_order' => 1,
            'maximum_order' => 100,
        ], $overrides));
    }

    public function test_guest_can_view_empty_cart(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('শপিং কার্ট')
            ->assertSee('আপনার কার্ট বর্তমানে খালি।');
    }

    public function test_customer_can_view_empty_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('শপিং কার্ট');
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = $this->createActiveProduct();

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'পণ্যটি কার্টে যোগ করা হয়েছে।',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_customer_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();

        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
            ->assertNotFound();
    }

    public function test_cannot_add_nonexistent_product(): void
    {
        $this->postJson(route('cart.add'), [
            'product_id' => 99999,
            'quantity' => 1,
        ])
            ->assertUnprocessable();
    }

    public function test_can_add_product_with_variant(): void
    {
        $product = $this->createActiveProduct();
        $variant = $this->createActiveVariant($product);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 150.00,
        ]);
    }

    public function test_cannot_add_inactive_variant(): void
    {
        $product = $this->createActiveProduct();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => false,
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])
            ->assertNotFound();
    }

    public function test_cannot_add_nonexistent_variant(): void
    {
        $product = $this->createActiveProduct();

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => 99999,
            'quantity' => 1,
        ])
            ->assertUnprocessable();
    }

    public function test_quantity_validation_rejects_zero(): void
    {
        $product = $this->createActiveProduct();

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 0,
        ])
            ->assertUnprocessable();
    }

    public function test_quantity_validation_rejects_negative(): void
    {
        $product = $this->createActiveProduct();

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => -1,
        ])
            ->assertUnprocessable();
    }

    public function test_price_is_set_server_side_not_from_request(): void
    {
        $product = $this->createActiveProduct(['base_price' => 100.00]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'unit_price' => 100.00,
        ]);
    }

    public function test_variant_price_is_set_server_side(): void
    {
        $product = $this->createActiveProduct();
        $variant = $this->createActiveVariant($product, ['price' => 250.00]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 100.00,
        ])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'unit_price' => 250.00,
        ]);
    }

    public function test_duplicate_item_increases_quantity(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();

        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_customer_can_update_quantity(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createActiveProduct();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->base_price,
        ]);

        $this->actingAs($user)->putJson(route('cart.update', $cartItem), [
            'quantity' => 5,
        ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_customer_cannot_update_other_customers_cart(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $cart1 = Cart::factory()->create(['user_id' => $user1->id]);
        $product = $this->createActiveProduct();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->base_price,
        ]);

        $this->actingAs($user2)->putJson(route('cart.update', $cartItem), [
            'quantity' => 5,
        ])
            ->assertForbidden();
    }

    public function test_customer_can_remove_item(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createActiveProduct();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->base_price,
        ]);

        $this->actingAs($user)->deleteJson(route('cart.remove', $cartItem))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_customer_cannot_remove_other_customers_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $cart1 = Cart::factory()->create(['user_id' => $user1->id]);
        $product = $this->createActiveProduct();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user2)->deleteJson(route('cart.remove', $cartItem))
            ->assertForbidden();
    }

    public function test_cart_subtotal_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product1 = $this->createActiveProduct(['base_price' => 100.00]);
        $product2 = $this->createActiveProduct(['base_price' => 250.00]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 100.00,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 250.00,
        ]);

        $cart->load('items');

        $this->assertEquals(450.00, $cart->subtotal);
        $this->assertEquals(3, $cart->item_count);
    }

    public function test_cart_page_shows_items(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createActiveProduct();

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->base_price,
        ]);

        $this->actingAs($user)->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('অর্ডার সারসংক্ষেপ');
    }

    public function test_empty_cart_shows_empty_message(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('আপনার কার্ট বর্তমানে খালি।')
            ->assertSee('কেনাকাটা চালিয়ে যান');
    }

    public function test_cart_item_with_discounted_product_uses_discount_price(): void
    {
        $product = $this->createActiveProduct([
            'base_price' => 200.00,
            'discount_price' => 150.00,
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'unit_price' => 150.00,
        ]);
    }

    public function test_cart_item_with_discounted_variant_uses_discount_price(): void
    {
        $product = $this->createActiveProduct();
        $variant = $this->createActiveVariant($product, [
            'price' => 300.00,
            'discount_price' => 225.00,
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'unit_price' => 225.00,
        ]);
    }
}
