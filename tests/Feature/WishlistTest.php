<?php

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\WishlistItem;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full access', 'permissions' => [], 'is_system' => true]
        );
    }

    private function createActiveProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'is_active' => true,
            'base_price' => 100.00,
        ], $overrides));
    }

    // ===================== Authentication gate =====================

    public function test_unauthenticated_user_cannot_view_wishlist(): void
    {
        $this->get(route('wishlist.index'))->assertRedirect(route('login'));
    }

    public function test_guest_wishlist_add_gets_bengali_login_prompt(): void
    {
        $product = $this->createActiveProduct();

        $this->postJson(route('wishlist.store'), ['product_id' => $product->id])
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => __('cart.wishlist.login_required'),
            ]);

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    // ===================== Add / duplicate =====================

    public function test_customer_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();

        $this->actingAs($user)->postJson(route('wishlist.store'), ['product_id' => $product->id])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('cart.wishlist.added'),
                'saved' => true,
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

        $this->actingAs($user)->postJson(route('wishlist.store'), ['product_id' => $product->id])->assertOk();
        $this->actingAs($user)->postJson(route('wishlist.store'), ['product_id' => $product->id])
            ->assertOk();

        // unique [user_id, product_id] — একটিই row থাকবে
        $this->assertEquals(1, WishlistItem::where('user_id', $user->id)
            ->where('product_id', $product->id)->count());
    }

    public function test_inactive_product_cannot_be_wishlisted(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->inactive()->create();

        $this->actingAs($user)->postJson(route('wishlist.store'), ['product_id' => $product->id])
            ->assertNotFound();

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    // ===================== Remove =====================

    public function test_customer_can_remove_wishlist_item(): void
    {
        $user = User::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $this->createActiveProduct()->id,
        ]);

        $this->actingAs($user)->deleteJson(route('wishlist.destroy', $wishlistItem))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
    }

    public function test_customer_cannot_remove_another_customers_wishlist_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $owner->id,
            'product_id' => $this->createActiveProduct()->id,
        ]);

        $this->actingAs($intruder)->deleteJson(route('wishlist.destroy', $wishlistItem))
            ->assertForbidden();

        $this->assertDatabaseHas('wishlist_items', ['id' => $wishlistItem->id]);
    }

    // ===================== Page & states =====================

    public function test_empty_wishlist_shows_bengali_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('আমার ইচ্ছেতালিকা')
            ->assertSee('আপনার ইচ্ছেতালিকা খালি')
            ->assertSee('পণ্য দেখুন');
    }

    public function test_wishlist_page_shows_items_with_default_variant_price(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct(['name' => 'নাজিরশাইল চাল']);
        ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'name' => '১ কেজি',
            'price' => 120,
        ]);
        WishlistItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $content = $this->actingAs($user)->get(route('wishlist.index'))->getContent();

        $this->assertStringContainsString('নাজিরশাইল চাল', $content);
        $this->assertStringContainsString(BengaliNumber::money(120), $content);
        $this->assertStringContainsString('কার্টে নিন', $content);
        $this->assertStringContainsString(__('cart.wishlist.remove'), $content);
    }

    // ===================== Move to cart =====================

    public function test_move_to_cart_uses_active_default_variant(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $default = ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'price' => 380,
        ]);
        Inventory::create(['product_variant_id' => $default->id, 'quantity' => 5]);
        $other = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'WL-OTH']);
        Inventory::create(['product_variant_id' => $other->id, 'quantity' => 5]);

        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('cart.wishlist.moved_to_cart'),
            ]);

        // ডিফল্ট variant-ই কার্টে গেছে; wishlist item সরেছে
        $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $default->id,
            'quantity' => 1,
            'unit_price' => 380,
        ]);
        $this->assertDatabaseMissing('cart_items', ['product_variant_id' => $other->id]);
    }

    public function test_move_to_cart_fails_without_active_default_variant(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        // শুধু inactive variant — active default নেই
        ProductVariant::factory()->inactive()->create(['product_id' => $product->id]);

        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('cart.errors.no_variant'),
            ]);

        $this->assertDatabaseCount('cart_items', 0);
        // ব্যর্থ হলে wishlist item অপরিবর্তিত
        $this->assertDatabaseHas('wishlist_items', ['id' => $wishlistItem->id]);
    }

    public function test_move_to_cart_fails_for_out_of_stock_default_variant(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        Inventory::create(['product_variant_id' => $variant->id, 'quantity' => 0]);

        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertStatus(422);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_cannot_move_another_customers_wishlist_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $owner->id,
            'product_id' => $this->createActiveProduct()->id,
        ]);

        $this->actingAs($intruder)->postJson(route('wishlist.move-to-cart', $wishlistItem))
            ->assertForbidden();

        $this->assertDatabaseCount('cart_items', 0);
    }

    // ===================== Inactive product handling (§38) =====================

    public function test_inactive_saved_product_stays_listed_as_unavailable(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        WishlistItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $product->update(['is_active' => false]);

        $content = $this->actingAs($user)->get(route('wishlist.index'))->getContent();

        $this->assertStringContainsString($product->name, $content);
        $this->assertStringContainsString('এই পণ্যটি বর্তমানে পাওয়া যাচ্ছে না।', $content);
    }

    // ===================== Bengali UI audit =====================

    public function test_wishlist_page_contains_no_unintended_english_ui_strings(): void
    {
        $user = User::factory()->create();
        $product = $this->createActiveProduct();
        ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        WishlistItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $forbidden = [
            'My Wishlist', 'Add to Cart', 'Move to Cart', 'Remove from Wishlist',
            'Your wishlist is empty', 'View Products', 'Price',
        ];

        $response = $this->actingAs($user)->get(route('wishlist.index'));
        $response->assertOk();

        foreach ($forbidden as $needle) {
            $this->assertStringNotContainsString($needle, $response->getContent(), "\"{$needle}\" found");
        }
    }
}
