<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
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
        ], $overrides));
    }

    public function test_unauthenticated_user_cannot_view_wishlist(): void
    {
        $this->get(route('wishlist.index'))
            ->assertRedirect();
    }

    public function test_authenticated_customer_can_view_empty_wishlist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('আমার ইচ্ছেতালিকা')
            ->assertSee('আপনার ইচ্ছেতালিকায় এখনো কোনো পণ্য নেই।');
    }

    public function test_customer_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();

        $this->actingAs($user)->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'পণ্যটি আপনার ইচ্ছেতালিকায় যোগ করা হয়েছে।',
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_duplicate_wishlist_entry_is_prevented(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();

        $this->actingAs($user)->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
        ])->assertOk();

        $this->actingAs($user)->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
        ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'পণ্যটি ইতোমধ্যে আপনার ইচ্ছেতালিকায় রয়েছে।',
        ]);
    }

    public function test_cannot_add_inactive_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => false]);

        $this->actingAs($user)->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
        ])
        ->assertNotFound();
    }

    public function test_cannot_add_nonexistent_product_to_wishlist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('wishlist.store'), [
            'product_id' => 99999,
        ])
        ->assertUnprocessable();
    }

    public function test_customer_can_add_product_with_variant(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $variant = $this->createActiveVariant($product);

        $this->actingAs($user)->postJson(route('wishlist.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_customer_can_remove_wishlist_item(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->deleteJson(route('wishlist.destroy', $wishlistItem))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'পণ্যটি ইচ্ছেতালিকা থেকে সরানো হয়েছে।',
            ]);

        $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
    }

    public function test_customer_cannot_remove_another_customers_wishlist_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = $this->createActiveProduct();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user2)->deleteJson(route('wishlist.destroy', $wishlistItem))
            ->assertForbidden();
    }

    public function test_wishlist_shows_current_product_information(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct(['name' => 'নাজিরশাইল চাল']);
        WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('নাজিরশাইল চাল')
            ->assertSee('উপলব্ধ');
    }

    public function test_inactive_product_is_handled_correctly(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $product->update(['is_active' => false]);

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('বর্তমানে উপলব্ধ নয়');
    }

    public function test_move_to_cart_uses_existing_cart_functionality(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'পণ্যটি কার্টে যোগ করা হয়েছে।',
            ]);

        $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_move_to_cart_removes_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertOk();

        $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
    }

    public function test_move_to_cart_fails_for_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $product->update(['is_active' => false]);

        $this->actingAs($user)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'পণ্যটি বর্তমানে উপলব্ধ নেই।',
            ]);
    }

    public function test_customer_cannot_move_another_customers_wishlist_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = $this->createActiveProduct();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user2)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertForbidden();
    }

    public function test_empty_wishlist_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('আপনার ইচ্ছেতালিকায় এখনো কোনো পণ্য নেই।')
            ->assertSee('পণ্য দেখুন');
    }

    public function test_wishlist_page_shows_items(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('কার্টে নিন')
            ->assertSee('ইচ্ছেতালিকা থেকে সরান');
    }
}
