<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function productAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('product-manager');

    return $user;
}

test('guest is redirected from admin product pages', function () {
    $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    $this->get(route('admin.products.create'))->assertRedirect(route('login'));
});

test('non permitted user cannot access admin products', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.products.index'))
        ->assertForbidden();
});

test('admin can create a product', function () {
    $category = Category::factory()->create();

    $this->actingAs(productAdminUser())
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'প্রিমিয়াম নাজিরশাইল চাল',
            'sku' => 'RICE-001',
            'base_price' => 150.00,
            'unit' => 'কেজি',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.products.index'));

    expect(Product::where('sku', 'RICE-001')->exists())->toBeTrue();
});

test('product supports category relationship', function () {
    $category = Category::factory()->create(['name' => 'চাল']);
    $product = Product::factory()->create(['category_id' => $category->id]);

    expect($product->category->is($category))->toBeTrue();
    expect($category->products()->first()->is($product))->toBeTrue();
});

test('duplicate sku is rejected', function () {
    Product::factory()->create(['sku' => 'RICE-001']);

    $category = Category::factory()->create();

    $this->actingAs(productAdminUser())
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'পণ্য ২',
            'sku' => 'RICE-001',
            'base_price' => 100,
        ])
        ->assertSessionHasErrors('sku');
});

test('duplicate slug is rejected', function () {
    Product::factory()->create(['slug' => 'premium-rice']);

    $category = Category::factory()->create();

    $this->actingAs(productAdminUser())
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'পণ্য ২',
            'sku' => 'RICE-002',
            'slug' => 'premium-rice',
            'base_price' => 100,
        ])
        ->assertSessionHasErrors('slug');
});

test('invalid category is rejected', function () {
    $this->actingAs(productAdminUser())
        ->post(route('admin.products.store'), [
            'category_id' => 9999,
            'name' => 'পণ্য',
            'sku' => 'RICE-003',
            'base_price' => 100,
        ])
        ->assertSessionHasErrors('category_id');
});

test('discount price must be less than base price', function () {
    $category = Category::factory()->create();

    $this->actingAs(productAdminUser())
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'পণ্য',
            'sku' => 'RICE-004',
            'base_price' => 100,
            'discount_price' => 150,
        ])
        ->assertSessionHasErrors('discount_price');
});

test('inactive products are excluded from public browsing', function () {
    Product::factory()->create(['slug' => 'active-product', 'is_active' => true]);
    Product::factory()->create(['slug' => 'inactive-product', 'is_active' => false]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('active-product')
        ->assertDontSee('inactive-product');
});

test('inactive product is not viewable publicly', function () {
    $product = Product::factory()->create(['slug' => 'hidden-product', 'is_active' => false]);

    $this->get(route('products.show', $product->slug))->assertNotFound();
});

test('active product is viewable publicly', function () {
    $product = Product::factory()->create(['slug' => 'visible-product', 'is_active' => true]);

    $this->get(route('products.show', $product->slug))->assertOk()->assertSee($product->name);
});

test('product search works by name', function () {
    Product::factory()->create(['name' => 'নাজিরশাইল চাল']);
    Product::factory()->create(['name' => 'দেশি মাছ']);

    $this->get(route('products.index', ['search' => 'নাজিরশাইল']))
        ->assertOk()
        ->assertSee('নাজিরশাইল')
        ->assertDontSee('দেশি মাছ');
});

test('product search works by sku', function () {
    Product::factory()->create(['name' => 'চাল', 'sku' => 'RICE-SPECIAL-001']);
    Product::factory()->create(['name' => 'মাছ', 'sku' => 'FISH-001']);

    $this->get(route('products.index', ['search' => 'RICE-SPECIAL']))
        ->assertOk()
        ->assertSee('চাল')
        ->assertDontSee('মাছ');
});

test('admin can update a product', function () {
    $product = Product::factory()->create();

    $this->actingAs(productAdminUser())
        ->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id,
            'name' => 'আপডেটেড পণ্য',
            'sku' => $product->sku,
            'base_price' => 200,
        ])
        ->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->name)->toBe('আপডেটেড পণ্য');
});

test('admin can delete a product', function () {
    $product = Product::factory()->create();

    $this->actingAs(productAdminUser())
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->trashed())->toBeTrue();
});

test('product has discount helper', function () {
    $product = Product::factory()->create(['base_price' => 100, 'discount_price' => 80]);
    expect($product->hasDiscount())->toBeTrue();

    $product2 = Product::factory()->create(['base_price' => 100, 'discount_price' => null]);
    expect($product2->hasDiscount())->toBeFalse();
});

test('product category breadcrumb works', function () {
    $grandparent = Category::factory()->create(['name' => 'চাল']);
    $parent = Category::factory()->create(['name' => 'স্থানীয় চাল', 'parent_id' => $grandparent->id]);
    $child = Category::factory()->create(['name' => 'নাজিরশাইল', 'parent_id' => $parent->id]);
    $product = Product::factory()->create(['category_id' => $child->id]);

    $breadcrumb = $product->getCategoryBreadcrumb();

    expect($breadcrumb->pluck('name')->toArray())->toBe(['চাল', 'স্থানীয় চাল', 'নাজিরশাইল']);
});
