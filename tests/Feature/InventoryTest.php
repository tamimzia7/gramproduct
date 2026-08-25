<?php

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function inventoryAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('inventory-manager');

    return $user;
}

test('guest is redirected from admin inventory pages', function () {
    $this->get(route('admin.inventories.index'))->assertRedirect(route('login'));
    $this->get(route('admin.inventories.create'))->assertRedirect(route('login'));
});

test('non permitted user cannot access admin inventories', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.inventories.index'))
        ->assertForbidden();
});

test('admin can view inventory list', function () {
    $this->actingAs(inventoryAdminUser())
        ->get(route('admin.inventories.index'))
        ->assertOk();
});

test('admin can view create inventory form', function () {
    $this->actingAs(inventoryAdminUser())
        ->get(route('admin.inventories.create'))
        ->assertOk();
});

test('admin can add stock', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 50,
            'reason' => 'নতুন ব্যাচ এসেছে',
        ])
        ->assertRedirect(route('admin.inventories.index'));

    $inventory = Inventory::where('product_id', $product->id)->first();
    expect($inventory)->not->toBeNull();
    expect($inventory->quantity)->toBe(50);
    expect($inventory->is_in_stock)->toBeTrue();
});

test('stock adjustment creates history record', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 100,
            'reason' => 'প্রাথমিক মজুদ',
        ]);

    $inventory = Inventory::where('product_id', $product->id)->first();
    expect($inventory->adjustments()->count())->toBe(1);
    expect($inventory->adjustments()->first()->type)->toBe(StockAdjustment::TYPE_STOCK_IN);
});

test('admin can adjust stock', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

    $inventory = Inventory::where('product_id', $product->id)->first();

    $this->actingAs(inventoryAdminUser())
        ->put(route('admin.inventories.adjust', $inventory), [
            'quantity' => 75,
            'reason' => 'ম্যানুয়াল সমন্বয়',
        ])
        ->assertRedirect(route('admin.inventories.index'));

    expect($inventory->fresh()->quantity)->toBe(75);
});

test('low stock is detected', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

    $inventory = Inventory::where('product_id', $product->id)->first();
    expect($inventory->isLowStock())->toBeTrue();
});

test('out of stock is detected', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

    $inventory = Inventory::where('product_id', $product->id)->first();
    $inventory->update(['quantity' => 0, 'is_in_stock' => false]);

    expect($inventory->fresh()->isOutOfStock())->toBeTrue();
});

test('available quantity excludes reserved', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

    $inventory = Inventory::where('product_id', $product->id)->first();
    $inventory->update(['reserved_quantity' => 10]);

    expect($inventory->fresh()->available_quantity)->toBe(40);
});

test('inventory can have variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 25,
        ])
        ->assertRedirect(route('admin.inventories.index'));

    $inventory = Inventory::where('product_variant_id', $variant->id)->first();
    expect($inventory)->not->toBeNull();
    expect($inventory->quantity)->toBe(25);
});

test('duplicate product variant inventory is rejected', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 25,
        ]);

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);
});

test('inventory filtering works', function () {
    $product1 = Product::factory()->create(['name' => 'চাল']);
    $product2 = Product::factory()->create(['name' => 'মাছ']);

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product1->id,
            'quantity' => 50,
        ]);

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product2->id,
            'quantity' => 0,
        ]);

    $this->actingAs(inventoryAdminUser())
        ->get(route('admin.inventories.index', ['search' => 'চাল']))
        ->assertOk()
        ->assertSee('চাল')
        ->assertDontSee('মাছ');
});

test('admin can view inventory history', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

    $inventory = Inventory::where('product_id', $product->id)->first();

    $this->actingAs(inventoryAdminUser())
        ->get(route('admin.inventories.history', $inventory))
        ->assertOk();
});

test('validation requires product_id and quantity', function () {
    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [])
        ->assertSessionHasErrors('product_id', 'quantity');
});

test('validation requires quantity to be at least 1', function () {
    $product = Product::factory()->create();

    $this->actingAs(inventoryAdminUser())
        ->post(route('admin.inventories.store'), [
            'product_id' => $product->id,
            'quantity' => 0,
        ])
        ->assertSessionHasErrors('quantity');
});
