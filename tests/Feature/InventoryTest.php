<?php

use App\Enums\InventoryTransactionType;
use App\Exceptions\InventoryException;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductVariantService;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryTest extends TestCase
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

    private function createAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');

        return $user;
    }

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

    private function service(): InventoryService
    {
        return app(InventoryService::class);
    }

    /**
     * নির্দিষ্ট স্টক-স্টেটসহ ভ্যারিয়েন্ট
     */
    private function makeVariantWithStock(int $quantity, int $reserved = 0, int $threshold = 10): ProductVariant
    {
        $variant = ProductVariant::factory()->create();

        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'low_stock_threshold' => $threshold,
        ]);

        return $variant->refresh();
    }

    // ===================== Core service =====================

    public function test_inventory_is_created_for_new_variant(): void
    {
        $variant = app(ProductVariantService::class)->create(
            Product::factory()->create(),
            [
                'name' => '১ কেজি',
                'sku' => 'INV-AUTO-1',
                'unit' => 'kg',
                'quantity' => 1,
                'price' => 120,
                'stock_status' => 'in_stock',
                'is_active' => true,
            ],
        );

        $this->assertDatabaseHas('inventories', [
            'product_variant_id' => $variant->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }

    public function test_stock_can_be_added_with_restock_transaction(): void
    {
        $admin = $this->createAdmin();
        $variant = ProductVariant::factory()->create();

        $this->service()->addStock($variant, 50, 'সরবরাহকারী চালান', $admin);

        $inventory = $variant->inventory()->first();
        $this->assertEquals(50, $inventory->quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_variant_id' => $variant->id,
            'type' => 'restock',
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'note' => 'সরবরাহকারী চালান',
            'created_by' => $admin->id,
        ]);
    }

    public function test_stock_can_be_removed_as_damaged_and_expired(): void
    {
        $admin = $this->createAdmin();
        $variant = $this->makeVariantWithStock(50);

        $this->service()->removeStock($variant, 3, InventoryTransactionType::DAMAGE, 'ভাঙা প্যাকেট', $admin);
        $this->service()->removeStock($variant, 2, InventoryTransactionType::EXPIRED, 'মেয়াদ শেষ', $admin);

        $this->assertEquals(45, $variant->fresh()->inventory->quantity);

        foreach (['damage' => -3, 'expired' => -2] as $type => $delta) {
            $this->assertDatabaseHas('inventory_transactions', [
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $delta,
            ]);
        }
    }

    public function test_stock_adjustment_works_both_directions(): void
    {
        $variant = $this->makeVariantWithStock(100);

        $this->service()->adjustStock($variant, -5, 'গুদামে ৫টি পণ্য কম পাওয়া গেছে।');
        $this->assertEquals(95, $variant->fresh()->inventory->quantity);

        $this->service()->adjustStock($variant, 20, 'অতিরিক্ত গুদামজাত');
        $this->assertEquals(115, $variant->fresh()->inventory->quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'adjustment',
            'quantity' => -5,
            'stock_before' => 100,
            'stock_after' => 95,
        ]);
    }

    // ===================== Guards / invariants =====================

    public function test_negative_stock_is_prevented(): void
    {
        $variant = $this->makeVariantWithStock(10);

        $this->expectException(InventoryException::class);
        $this->service()->removeStock($variant, 11);
    }

    public function test_failed_operation_writes_nothing_partially(): void
    {
        $variant = $this->makeVariantWithStock(10);

        try {
            $this->service()->removeStock($variant, 99);
            $this->fail('InventoryException expected.');
        } catch (InventoryException) {
        }

        $this->assertEquals(10, $variant->fresh()->inventory->quantity);
        $this->assertEquals(0, InventoryTransaction::where('product_variant_id', $variant->id)->count());
    }

    public function test_reserved_cannot_exceed_current_stock(): void
    {
        $variant = $this->makeVariantWithStock(10);

        try {
            $this->service()->reserve($variant, 11);
            $this->fail('InventoryException expected.');
        } catch (InventoryException) {
        }

        $this->assertEquals(0, $variant->fresh()->inventory->reserved_quantity);
    }

    public function test_available_quantity_calculated_correctly(): void
    {
        $variant = $this->makeVariantWithStock(100, 20);

        $this->assertEquals(80, $variant->availableQuantity());
        $this->assertEquals(80, $this->service()->availableQuantity($variant));
    }

    public function test_low_stock_status_works(): void
    {
        $variant = $this->makeVariantWithStock(8, 0, 10);

        $this->assertTrue($variant->isLowStock());
        $this->assertFalse($variant->isOutOfStock());

        // সীমার ঠিক উপরে থাকলে low নয়
        $ok = $this->makeVariantWithStock(11, 0, 10);
        $this->assertFalse($ok->isLowStock());
    }

    public function test_out_of_stock_status_respects_backorder(): void
    {
        $variant = $this->makeVariantWithStock(0, 0, 5);
        $this->assertTrue($variant->isOutOfStock());
        $this->assertFalse($variant->isPurchasable());

        // backorder চালু থাকলে কেনা যাবে
        Inventory::where('product_variant_id', $variant->id)->update(['allow_backorder' => true]);
        $this->assertFalse($variant->refresh()->isOutOfStock());
        $this->assertTrue($variant->isPurchasable());
    }

    // ===================== Reservation =====================

    public function test_reservation_works(): void
    {
        $variant = $this->makeVariantWithStock(100);

        $this->service()->reserve($variant, 5, ['type' => 'order', 'id' => 77], 'অর্ডার #৭৭');

        $inventory = $variant->fresh()->inventory;
        $this->assertEquals(100, $inventory->quantity);
        $this->assertEquals(5, $inventory->reserved_quantity);
        $this->assertEquals(95, $inventory->available_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'reservation',
            'reference_type' => 'order',
            'reference_id' => 77,
        ]);
    }

    public function test_reservation_release_works(): void
    {
        $variant = $this->makeVariantWithStock(100, 5);

        $this->service()->releaseReservation($variant, 5);

        $inventory = $variant->fresh()->inventory;
        $this->assertEquals(0, $inventory->reserved_quantity);
        $this->assertEquals(100, $inventory->available_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_variant_id' => $variant->id,
            'type' => 'reservation_release',
            'quantity' => 5,
        ]);
    }

    public function test_cannot_release_more_than_reserved(): void
    {
        $variant = $this->makeVariantWithStock(50, 5);

        $this->expectException(InventoryException::class);
        $this->service()->releaseReservation($variant, 6);
    }

    // ===================== Auditability =====================

    public function test_transaction_records_full_audit_information(): void
    {
        $admin = $this->createAdmin();
        $variant = ProductVariant::factory()->create();

        $this->service()->addStock($variant, 30, 'প্রথম ক্রয়', $admin);

        $transaction = InventoryTransaction::query()
            ->forVariant($variant->id)
            ->firstOrFail();

        $this->assertSame(InventoryTransactionType::RESTOCK, $transaction->type);
        $this->assertEquals(30, $transaction->quantity);
        $this->assertEquals(0, $transaction->stock_before);
        $this->assertEquals(30, $transaction->stock_after);
        $this->assertEquals($admin->id, $transaction->created_by);
        $this->assertEquals('প্রথম ক্রয়', $transaction->note);
        $this->assertNotNull($transaction->created_at);
    }

    public function test_inventory_change_and_transaction_rollback_together(): void
    {
        $variant = $this->makeVariantWithStock(10);

        try {
            DB::transaction(function () use ($variant): void {
                $this->service()->addStock($variant, 25, 'রোলব্যাক যাচাই');

                throw new RuntimeException('বাইরের ব্যর্থতা সিমুলেশন');
            });
        } catch (RuntimeException) {
        }

        // inventory ও transaction — দুটোই একসাথে বাতিল হয়েছে
        $this->assertEquals(10, $variant->fresh()->inventory->quantity);
        $this->assertEquals(0, InventoryTransaction::where('product_variant_id', $variant->id)->count());
    }

    // ===================== Concurrency safety =====================

    public function test_availability_is_verified_immediately_before_each_change(): void
    {
        $variant = $this->makeVariantWithStock(100);

        // ধারাবাহিক সংরক্ষণ — প্রতিটি অপারেশন সর্বশেষ স্টক পড়ে যাচাই করে
        $this->service()->reserve($variant, 60);
        $this->service()->reserve($variant, 40);

        $this->assertEquals(0, $variant->fresh()->inventory->available_quantity);

        $this->expectException(InventoryException::class);
        $this->service()->reserve($variant, 1);
    }

    public function test_reserved_stock_blocks_manual_removal_from_available(): void
    {
        $variant = $this->makeVariantWithStock(100, 90);

        // available = 10; 20 কমানো যাবে না — সংরক্ষিত স্টক রক্ষা পায়
        $this->expectException(InventoryException::class);
        $this->service()->removeStock($variant, 20, InventoryTransactionType::DAMAGE);
    }

    // ===================== Return handling =====================

    public function test_restockable_return_adds_sellable_stock(): void
    {
        $admin = $this->createAdmin();
        $variant = $this->makeVariantWithStock(50);

        $result = $this->service()->handleReturn($variant, 2, true, null, $admin);

        $this->assertTrue($result['restocked']);
        $this->assertEquals(52, $variant->fresh()->inventory->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_variant_id' => $variant->id,
            'type' => 'return',
            'quantity' => 2,
        ]);
    }

    public function test_non_restockable_return_leaves_stock_unchanged(): void
    {
        $variant = $this->makeVariantWithStock(50);

        $result = $this->service()->handleReturn($variant, 2, false, 'ক্ষতিগ্রস্ত ফেরত');

        $this->assertFalse($result['restocked']);
        $this->assertEquals(50, $variant->fresh()->inventory->quantity);
        $this->assertEquals(0, InventoryTransaction::where('product_variant_id', $variant->id)->count());
    }

    // ===================== Authorization =====================

    public function test_guest_is_redirected_from_inventory_dashboard(): void
    {
        $this->get(route('admin.inventory.index'))->assertRedirect();
    }

    public function test_plain_customer_cannot_view_inventory(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('admin.inventory.index'))
            ->assertForbidden()
            ->assertSee('এই কাজটি করার অনুমতি আপনার নেই।');
    }

    public function test_user_without_view_permission_cannot_access_inventory_list(): void
    {
        $user = $this->createUserWithPermissions(['view-dashboard']);

        $this->actingAs($user)->get(route('admin.inventory.index'))->assertForbidden();
    }

    public function test_user_without_adjust_permission_cannot_submit_adjustment(): void
    {
        $user = $this->createUserWithPermissions(['view-dashboard', 'inventory.view']);
        $variant = $this->makeVariantWithStock(50);
        $inventory = $variant->inventory;

        $this->actingAs($user)->post(route('admin.inventory.adjust', $inventory), [
            'quantity' => -5,
            'reason' => 'অননুমোদিত সমন্বয়',
        ])->assertForbidden();

        $this->assertEquals(50, $variant->fresh()->inventory->quantity);
    }

    public function test_user_with_create_permission_can_add_stock_via_http(): void
    {
        $user = $this->createUserWithPermissions(['view-dashboard', 'inventory.view', 'inventory.create']);
        $variant = $this->makeVariantWithStock(10);
        $inventory = $variant->inventory;

        $this->actingAs($user)->post(route('admin.inventory.add', $inventory), [
            'quantity' => 20,
            'note' => 'নতুন চালান',
        ])->assertRedirect();

        $this->assertEquals(30, $variant->fresh()->inventory->quantity);
        $this->assertEquals($user->id, InventoryTransaction::latest('id')->first()->created_by);
    }

    // ===================== Admin pages (Bengali UI) =====================

    public function test_admin_can_view_inventory_dashboard_with_stats(): void
    {
        $admin = $this->createAdmin();
        $this->makeVariantWithStock(120, 10, 20);

        $this->actingAs($admin)->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('মোট পণ্য')
            ->assertSee('স্টকে আছে')
            ->assertSee('স্টক কম')
            ->assertSee('স্টক শেষ')
            ->assertSee('মোট স্টক')
            ->assertSee('সংরক্ষিত স্টক')
            ->assertSee(BengaliNumber::format(110)); // available ১১০
    }

    public function test_admin_can_view_inventory_detail_with_history(): void
    {
        $admin = $this->createAdmin();
        $variant = $this->makeVariantWithStock(50);

        $this->service()->addStock($variant, 20, 'দ্বিতীয় চালান', $admin);

        $this->actingAs($admin)->get(route('admin.inventory.show', $variant->inventory))
            ->assertOk()
            ->assertSee('লেনদেন ইতিহাস')
            ->assertSee('স্টক যোগ')
            ->assertSee('দ্বিতীয় চালান')
            ->assertSee($admin->name);
    }

    public function test_add_stock_form_validation_returns_bengali_message(): void
    {
        $admin = $this->createAdmin();
        $variant = $this->makeVariantWithStock(10);
        $inventory = $variant->inventory;

        $response = $this->actingAs($admin)->post(route('admin.inventory.add', $inventory), [
            'quantity' => '',
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertStringContainsString('যোগ করার পরিমাণ লিখুন', session('errors')->first('quantity'));
    }

    public function test_adjust_below_reserved_is_rejected_with_bengali_message(): void
    {
        $admin = $this->createAdmin();
        $variant = $this->makeVariantWithStock(10, 8);
        $inventory = $variant->inventory;

        $this->actingAs($admin)->post(route('admin.inventory.adjust', $inventory), [
            'quantity' => -9,
            'reason' => 'ভুল সমন্বয়',
        ])->assertRedirect();

        $response2 = $this->actingAs($admin)->get(route('admin.inventory.adjust-form', $inventory));
        $response2->assertOk();

        // ফর্মে ফেরত এসেই বাংলা ত্রুটি-বার্তা দেখা যায়
        $this->assertStringContainsString(__('inventory.errors.below_reserved'), $response2->getContent());
    }

    // ===================== Customer-facing integration =====================

    public function test_product_page_shows_low_stock_label_for_selected_variant(): void
    {
        $product = Product::factory()->create(['name' => 'নাজিরশাইল চাল']);
        $variant = ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'name' => '৫ কেজি',
        ]);
        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('মাত্র ৫টি বাকি');
    }

    public function test_product_page_disables_cart_when_variant_out_of_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ]);

        $content = $this->get(route('products.show', $product))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('স্টক শেষ', $content);
        $this->assertMatchesRegularExpression('/"purchasable":false/u', $content);
        $this->assertMatchesRegularExpression('/add-to-cart-btn[^>]*disabled/u', $content);
    }

    public function test_product_card_shows_low_stock_badge_only_when_needed(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'price' => 570,
        ]);
        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 6,
            'low_stock_threshold' => 10,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            // ইনফরমেটিভ low-stock লেবেল — সর্বোচ্চ clarity
            ->assertSee('মাত্র ৬টি বাকি')
            // পরিচ্ছন্ন ডিজাইন — স্বাভাবিক স্টকে সংখ্যা দেখানো হয় না
            ->assertDontSee('স্টকে আছে');
    }

    // ===================== Bengali labels =====================

    public function test_transaction_type_labels_are_bengali(): void
    {
        $expected = [
            'purchase' => 'ক্রয়',
            'sale' => 'বিক্রয়',
            'return' => 'ফেরত',
            'adjustment' => 'সমন্বয়',
            'damage' => 'ক্ষতিগ্রস্ত',
            'expired' => 'মেয়াদোত্তীর্ণ',
            'restock' => 'স্টক যোগ',
            'reservation' => 'সংরক্ষণ',
            'reservation_release' => 'সংরক্ষণ বাতিল',
        ];

        foreach ($expected as $value => $label) {
            $this->assertEquals($label, InventoryTransactionType::from($value)->label());
        }
    }

    public function test_inventory_pages_contain_no_unintended_english_ui_strings(): void
    {
        $admin = $this->createAdmin();
        $variant = $this->makeVariantWithStock(50);
        $this->service()->addStock($variant, 10, 'চালান', $admin);

        $forbidden = [
            'Add Stock', 'Adjust Stock', 'View Details', 'Current Stock',
            'Reserved', 'Available Stock', 'Low Stock Threshold', 'Submit', 'Cancel',
        ];

        $urls = [
            route('admin.inventory.index'),
            route('admin.inventory.show', $variant->inventory),
            route('products.show', $variant->product),
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
