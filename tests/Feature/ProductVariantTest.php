<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductVariantService;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // টেস্টের জন্য super-admin রোল তৈরি
        Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full access', 'permissions' => [], 'is_system' => true]
        );
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');

        return $user;
    }

    /**
     * নির্দিষ্ট permission-যুক্ত ইউজার (super-admin নয়) — granular gate যাচাইয়ের জন্য
     */
    private function createUserWithPermissions(array $permissions): User
    {
        $role = Role::updateOrCreate(
            ['slug' => 'perm-'.md5(implode(',', $permissions))],
            ['name' => 'Perm Test', 'description' => '', 'permissions' => $permissions, 'is_system' => false]
        );

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'product_id' => Product::factory()->create()->id,
            'name' => '১ কেজি',
            'sku' => 'RICE-NS-1KG',
            'unit' => 'kg',
            'quantity' => 1,
            'price' => 120,
            'compare_at_price' => 140,
            'stock_status' => 'in_stock',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides);
    }

    private function storeRoute(Product $product): string
    {
        return route('admin.products.variants.store', $product);
    }

    // ===================== Authorization =====================

    public function test_guest_is_redirected_from_variant_create_form(): void
    {
        $product = Product::factory()->create();

        $this->get(route('admin.products.variants.create', $product))->assertRedirect();
    }

    public function test_plain_customer_cannot_create_variant(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $product = Product::factory()->create();

        $this->actingAs($user)->post($this->storeRoute($product), $this->validPayload([
            'product_id' => $product->id,
        ]))->assertForbidden();

        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_user_without_edit_permission_cannot_update_variant(): void
    {
        $user = $this->createUserWithPermissions(['view-dashboard', 'products.variants.view']);
        $variant = ProductVariant::factory()->create();

        $this->actingAs($user)->put(
            route('admin.products.variants.update', [$variant->product, $variant]),
            $this->validPayload(['name' => 'পরিবর্তিত']),
        )->assertForbidden();

        $this->assertNotSame('পরিবর্তিত', $variant->fresh()->name);
    }

    public function test_user_with_view_permission_can_see_variant_management_on_product_page(): void
    {
        $user = $this->createUserWithPermissions(['view-dashboard', 'products.view']);
        $product = Product::factory()->create(['name' => 'নাজিরশাইল চাল']);

        $this->actingAs($user)->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('ভ্যারিয়েন্টসমূহ');
    }

    public function test_unauthorized_variant_action_response_is_bengali_friendly(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $product = Product::factory()->create();

        $this->actingAs($user)->post($this->storeRoute($product), $this->validPayload())
            ->assertForbidden()
            ->assertSee('এই কাজটি করার অনুমতি আপনার নেই।');
    }

    // ===================== Admin CRUD =====================

    public function test_admin_can_create_variant(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload([
            'product_id' => $product->id,
        ]))->assertRedirect();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => '১ কেজি',
            'sku' => 'RICE-NS-1KG',
            'price' => 120,
        ]);
    }

    public function test_admin_can_update_variant(): void
    {
        $admin = $this->createAdmin();
        $variant = ProductVariant::factory()->create(['name' => '১ কেজি']);

        $response = $this->actingAs($admin)->put(
            route('admin.products.variants.update', [$variant->product, $variant]),
            $this->validPayload([
                'name' => '৫ কেজি',
                'quantity' => 5,
                'price' => 570,
                'compare_at_price' => null,
            ]),
        );

        $response->assertRedirect();

        $variant->refresh();
        $this->assertEquals('৫ কেজি', $variant->name);
        $this->assertEquals(5, (float) $variant->quantity);
        $this->assertEquals(570, (float) $variant->price);
    }

    public function test_admin_can_delete_variant_safely(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();
        $default = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'sort_order' => 1]);
        $other = ProductVariant::factory()->create(['product_id' => $product->id, 'sort_order' => 2]);

        $this->actingAs($admin)->delete(route('admin.products.variants.destroy', [$product, $default]))
            ->assertRedirect();

        $this->assertSoftDeleted('product_variants', ['id' => $default->id]);

        // ডিফল্ট মুছলে fallback সক্রিয় ভ্যারিয়েন্ট ডিফল্ট হয় — পণ্য কখনো অবৈধ অবস্থায় থাকে না
        $this->assertTrue((bool) $other->fresh()->is_default);
    }

    public function test_variant_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->assertTrue($variant->product->is($product));
        $this->assertTrue($product->variants->contains($variant));
    }

    public function test_quantity_label_formats_bengali_values_correctly(): void
    {
        $gram = ProductVariant::factory()->make([
            'quantity' => 500,
            'unit' => 'gram',
        ]);
        $hundredGram = ProductVariant::factory()->make([
            'quantity' => 100,
            'unit' => 'gram',
        ]);
        $fraction = ProductVariant::factory()->make([
            'quantity' => 2.5,
            'unit' => 'kg',
        ]);
        $bag = ProductVariant::factory()->make([
            'quantity' => 25,
            'unit' => 'bag',
        ]);

        $this->assertEquals('৫০০ গ্রাম', $gram->quantityLabel());
        // শেষের শূন্য পুরো সংখ্যা গিলে ফেলতে পারে না
        $this->assertEquals('১০০ গ্রাম', $hundredGram->quantityLabel());
        $this->assertEquals('২.৫ কেজি', $fraction->quantityLabel());
        $this->assertEquals('২৫ বস্তা', $bag->quantityLabel());
    }

    public function test_setting_new_default_unsets_old_default_via_endpoint(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();
        $first = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        $second = ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->actingAs($admin)->patch(route('admin.products.variants.default', [$product, $second]))
            ->assertRedirect();

        $this->assertFalse((bool) $first->fresh()->is_default);
        $this->assertTrue((bool) $second->fresh()->is_default);

        // শুধুমাত্র একটি ডিফল্ট থাকতে পারবে
        $this->assertEquals(1, $product->variants()->where('is_default', true)->count());
    }

    public function test_toggle_active_deactivates_and_reactivates(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        $fallback = ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->actingAs($admin)->patch(route('admin.products.variants.toggle-active', [$product, $variant]))
            ->assertRedirect();

        $variant->refresh();
        $this->assertFalse($variant->isActive());

        // ডিফল্ট নিষ্ক্রিয় হলে fallback ডিফল্ট হয়
        $this->assertTrue((bool) $fallback->fresh()->is_default);

        $this->actingAs($admin)->patch(route('admin.products.variants.toggle-active', [$product, $variant]))
            ->assertRedirect();

        $this->assertTrue((bool) $variant->fresh()->isActive());
    }

    public function test_first_created_active_variant_becomes_default_automatically(): void
    {
        $service = app(ProductVariantService::class);
        $product = Product::factory()->create();

        $variant = $service->create($product, [
            'name' => '১ কেজি',
            'sku' => 'AUTO-DEFAULT-1',
            'unit' => 'kg',
            'quantity' => 1,
            'price' => 120,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $this->assertTrue((bool) $variant->is_default);
    }

    // ===================== Validation (Bengali) =====================

    public function test_name_required_returns_bengali_message(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload(['name' => '', 'product_id' => $product->id]))
            ->assertSessionHasErrors('name');

        $this->assertStringContainsString('ভ্যারিয়েন্টের নাম লিখুন', session('errors')->first('name'));
    }

    public function test_sku_required_returns_bengali_message(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload(['sku' => '', 'product_id' => $product->id]))
            ->assertSessionHasErrors('sku');

        $this->assertStringContainsString('SKU লিখুন', session('errors')->first('sku'));
    }

    public function test_duplicate_sku_is_rejected_across_products(): void
    {
        ProductVariant::factory()->create(['sku' => 'DUP-SKU-001']);
        $admin = $this->createAdmin();
        $anotherProduct = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($anotherProduct), $this->validPayload([
            'sku' => 'DUP-SKU-001',
            'product_id' => $anotherProduct->id,
        ]))->assertSessionHasErrors('sku');

        $this->assertStringContainsString('এই SKU ইতোমধ্যে ব্যবহৃত হয়েছে', session('errors')->first('sku'));
    }

    public function test_soft_deleted_variant_sku_stays_reserved(): void
    {
        $variant = ProductVariant::factory()->create(['sku' => 'RESERVED-1']);
        $variant->delete();

        $admin = $this->createAdmin();
        $product = $variant->product;

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload([
            'sku' => 'RESERVED-1',
            'product_id' => $product->id,
        ]))->assertSessionHasErrors('sku');
    }

    public function test_invalid_product_rejected_with_bengali_message(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post($this->storeRoute(Product::factory()->create()), $this->validPayload([
            'product_id' => 99999,
        ]))->assertSessionHasErrors('product_id');

        $this->assertStringContainsString('নির্বাচিত পণ্যটি সঠিক নয়', session('errors')->first('product_id'));
    }

    public function test_quantity_must_be_greater_than_zero(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload(['quantity' => 0, 'product_id' => $product->id]))
            ->assertSessionHasErrors('quantity');

        $this->assertStringContainsString('পরিমাণ শূন্যের বেশি হতে হবে', session('errors')->first('quantity'));

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload(['quantity' => -2, 'product_id' => $product->id]))
            ->assertSessionHasErrors('quantity');
    }

    public function test_price_cannot_be_negative(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload(['price' => -5, 'product_id' => $product->id]))
            ->assertSessionHasErrors('price');

        $this->assertStringContainsString('মূল্য শূন্যের কম হতে পারবে না', session('errors')->first('price'));
    }

    public function test_compare_price_must_be_equal_or_greater_than_price(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload([
            'price' => 500,
            'compare_at_price' => 499,
            'product_id' => $product->id,
        ]))->assertSessionHasErrors('compare_at_price');

        $this->assertStringContainsString('আগের মূল্য বর্তমান মূল্যের সমান বা বেশি হতে হবে', session('errors')->first('compare_at_price'));

        // সমান হলে গ্রহণযোগ্য
        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload([
            'sku' => 'EQUAL-COMPARE-1',
            'price' => 500,
            'compare_at_price' => 500,
            'product_id' => $product->id,
        ]))->assertRedirect();
    }

    public function test_invalid_unit_and_stock_status_rejected(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload([
            'unit' => 'kilometer',
            'sku' => 'BAD-UNIT-1',
            'product_id' => $product->id,
        ]))->assertSessionHasErrors('unit');

        $this->assertStringContainsString('বৈধ একক নির্বাচন করুন', session('errors')->first('unit'));

        $this->actingAs($admin)->post($this->storeRoute($product), $this->validPayload([
            'stock_status' => 'unknown_status',
            'sku' => 'BAD-STOCK-1',
            'product_id' => $product->id,
        ]))->assertSessionHasErrors('stock_status');

        $this->assertStringContainsString('বৈধ স্ট্যাটাস নির্বাচন করুন', session('errors')->first('stock_status'));
    }

    public function test_update_keeps_own_sku_but_rejects_others(): void
    {
        $admin = $this->createAdmin();
        $variant = ProductVariant::factory()->create(['sku' => 'KEEP-ME-1']);

        $this->actingAs($admin)->put(
            route('admin.products.variants.update', [$variant->product, $variant]),
            $this->validPayload(['sku' => 'KEEP-ME-1']),
        )->assertRedirect();

        $other = ProductVariant::factory()->create(['sku' => 'OTHER-SKU-1']);

        $this->actingAs($admin)->put(
            route('admin.products.variants.update', [$variant->product, $variant]),
            $this->validPayload(['sku' => 'OTHER-SKU-1']),
        )->assertSessionHasErrors('sku');
    }

    // ===================== Default invariant (service) =====================

    public function test_service_create_with_default_flag_unsets_previous_default(): void
    {
        $service = app(ProductVariantService::class);
        $product = Product::factory()->create();

        $first = $service->create($product, [
            'name' => '১ কেজি', 'sku' => 'SVC-D-1', 'unit' => 'kg', 'quantity' => 1,
            'price' => 100, 'stock_status' => 'in_stock', 'is_default' => false, 'is_active' => true,
        ]);

        $second = $service->create($product, [
            'name' => '৫ কেজি', 'sku' => 'SVC-D-5', 'unit' => 'kg', 'quantity' => 5,
            'price' => 480, 'stock_status' => 'in_stock', 'is_default' => true, 'is_active' => true,
        ]);

        $this->assertFalse((bool) $first->refresh()->is_default);
        $this->assertTrue((bool) $second->is_default);
        $this->assertEquals(1, $product->variants()->where('is_default', true)->count());
    }

    public function test_only_one_default_exists_after_many_operations(): void
    {
        $service = app(ProductVariantService::class);
        $product = Product::factory()->create();

        $variants = [];
        foreach ([1, 5, 10] as $size) {
            $variants[] = $service->create($product, [
                'name' => $size.' কেজি',
                'sku' => 'INV-'.$size,
                'unit' => 'kg',
                'quantity' => $size,
                'price' => 100 * $size,
                'stock_status' => 'in_stock',
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        $service->delete($variants[2]);
        $service->setActive($variants[0], false);

        $defaults = $product->variants()->where('is_default', true)->get();
        $this->assertCount(1, $defaults);
        $this->assertTrue((bool) $defaults->first()->isActive());
    }

    // ===================== Customer product page =====================

    private function riceProductWithVariants(): Product
    {
        $product = Product::factory()->create([
            'name' => 'নাজিরশাইল চাল',
            'base_price' => 120,
            'unit' => 'kg',
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => '১ কেজি',
            'sku' => 'RICE-NS-1KG',
            'quantity' => 1,
            'price' => 120,
            'compare_at_price' => 140,
            'is_default' => true,
            'sort_order' => 1,
        ]);
        ProductVariant::factory()->outOfStock()->create([
            'product_id' => $product->id,
            'name' => '২৫ কেজি',
            'sku' => 'RICE-NS-25KG',
            'quantity' => 25,
            'price' => 2600,
            'sort_order' => 4,
        ]);

        return $product;
    }

    public function test_product_page_displays_variant_selector(): void
    {
        $product = $this->riceProductWithVariants();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('ভ্যারিয়েন্ট নির্বাচন করুন')
            ->assertSee('১ কেজি')
            ->assertSee('২৫ কেজি')
            ->assertSee(BengaliNumber::money(120))
            ->assertSee(BengaliNumber::money(140));
    }

    public function test_inactive_variant_hidden_from_customers(): void
    {
        $product = $this->riceProductWithVariants();
        $hidden = ProductVariant::factory()->inactive()->create([
            'product_id' => $product->id,
            'name' => 'গোপন ভ্যারিয়েন্ট',
            'sku' => 'HIDDEN-VAR-1',
            'price' => 99,
        ]);

        $response = $this->get(route('products.show', $product))->assertOk();

        $this->assertStringNotContainsString('গোপন ভ্যারিয়েন্ট', $response->getContent());
        $this->assertStringNotContainsString($hidden->sku, $response->getContent());
    }

    public function test_variant_payload_contains_price_availability_for_selection_js(): void
    {
        $product = $this->riceProductWithVariants();

        $content = $this->get(route('products.show', $product))->getContent();

        // JS payload — দুই ভ্যারিয়েন্টের মূল্য ও availability আগেই লোড থাকে; নির্বাচনে নতুন রিকুয়েস্ট লাগে না
        $this->assertMatchesRegularExpression('/"price":"'.preg_quote(BengaliNumber::money(120), '/').'"/u', $content);
        $this->assertMatchesRegularExpression('/"price":"'.preg_quote(BengaliNumber::money(2600), '/').'"/u', $content);
        $this->assertMatchesRegularExpression('/"purchasable":false/u', $content);
        $this->assertMatchesRegularExpression('/"purchasable":true/u', $content);
        $this->assertMatchesRegularExpression('/"stock_label":"স্টক শেষ"/u', $content);
    }

    public function test_pre_order_label_displayed_in_payload(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->preOrder()->default()->create(['product_id' => $product->id]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('প্রি-অর্ডার');
    }

    public function test_product_with_no_active_variants_appears_unavailable(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->inactive()->create(['product_id' => $product->id]);
        ProductVariant::factory()->create(['product_id' => $product->id])->delete();

        $content = $this->get(route('products.show', $product))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('এই পণ্যটি বর্তমানে সরবরাহের বাইরে আছে।', $content);
        $this->assertMatchesRegularExpression('/add-to-cart-btn[^>]*disabled/u', $content);
    }

    public function test_product_card_shows_default_variant_price_and_count(): void
    {
        $product = Product::factory()->create(['base_price' => 999]);
        ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'price' => 120,
            'unit' => 'kg',
            'sort_order' => 1,
        ]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 570, 'sort_order' => 2]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1100, 'sort_order' => 3]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee(BengaliNumber::money(120).' / কেজি')
            // পণ্যের base_price নয় — ডিফল্ট ভ্যারিয়েন্টের মূল্যই প্রদর্শিত হয়
            ->assertDontSee(BengaliNumber::money(999))
            ->assertSee('৩টি ভ্যারিয়েন্ট');
    }

    public function test_product_without_variants_still_shows_base_price_on_card(): void
    {
        Product::factory()->create(['name' => 'তাজা লাউ', 'base_price' => 45, 'discount_price' => null, 'unit' => 'piece']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee(BengaliNumber::money(45));
    }

    // ===================== Bengali UI audit =====================

    public function test_variant_pages_contain_no_unintended_english_ui_strings(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->has(ProductVariant::factory()->count(2), 'variants')->create();

        $forbidden = [
            'Add Variant', 'Edit Variant', 'Save', 'Delete', 'Cancel',
            'Default Variant', 'Active', 'Inactive', 'Out of Stock', 'Pre Order', 'Submit',
        ];

        foreach ($product->variants as $variant) {
            $urls = [
                route('admin.products.variants.create', $product),
                route('admin.products.variants.edit', [$product, $variant]),
                route('products.show', $product),
            ];

            foreach ($urls as $url) {
                $response = $this->actingAs($admin)->get($url);
                $response->assertOk();

                foreach ($forbidden as $needle) {
                    $this->assertStringNotContainsString($needle, $response->getContent(), "\"{$needle}\" found on {$url}");
                }
            }
        }
    }
}
