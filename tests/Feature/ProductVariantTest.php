<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function variantAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('product-manager');

    return $user;
}

function createVariantProduct(): Product
{
    $category = Category::factory()->create();

    return Product::factory()->create(['category_id' => $category->id]);
}

test('guest is redirected from admin variant pages', function () {
    $product = createVariantProduct();

    $this->get(route('admin.products.variants.index', $product))->assertRedirect(route('login'));
    $this->get(route('admin.products.variants.create', $product))->assertRedirect(route('login'));
});

test('non permitted user cannot access admin variants', function () {
    $product = createVariantProduct();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.products.variants.index', $product))
        ->assertForbidden();
});

test('admin can create a variant', function () {
    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->post(route('admin.products.variants.store', $product), [
            'name' => '১ কেজি',
            'sku' => 'RICE-1KG',
            'weight' => 1,
            'unit' => 'কেজি',
            'price' => 160,
            'minimum_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.products.variants.index', $product));

    expect(ProductVariant::where('sku', 'RICE-1KG')->exists())->toBeTrue();
    expect($product->variants()->count())->toBe(1);
});

test('variant supports product relationship', function () {
    $product = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    expect($variant->product->is($product))->toBeTrue();
    expect($product->variants()->first()->is($variant))->toBeTrue();
});

test('duplicate variant sku is rejected', function () {
    ProductVariant::factory()->create(['sku' => 'RICE-1KG']);

    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->post(route('admin.products.variants.store', $product), [
            'name' => '৫ কেজি',
            'sku' => 'RICE-1KG',
            'price' => 750,
        ])
        ->assertSessionHasErrors('sku');
});

test('invalid product is rejected for variant creation', function () {
    $this->actingAs(variantAdminUser())
        ->post(route('admin.products.variants.store', 9999), [
            'name' => '১ কেজি',
            'sku' => 'RICE-1KG',
            'price' => 160,
        ])
        ->assertNotFound();
});

test('variant price must be greater than zero', function () {
    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->post(route('admin.products.variants.store', $product), [
            'name' => '১ কেজি',
            'sku' => 'RICE-1KG',
            'price' => -10,
        ])
        ->assertSessionHasErrors('price');
});

test('discount price must be less than variant price', function () {
    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->post(route('admin.products.variants.store', $product), [
            'name' => '১ কেজি',
            'sku' => 'RICE-1KG',
            'price' => 100,
            'discount_price' => 150,
        ])
        ->assertSessionHasErrors('discount_price');
});

test('maximum order must be greater than or equal to minimum order', function () {
    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->post(route('admin.products.variants.store', $product), [
            'name' => '১ কেজি',
            'sku' => 'RICE-1KG',
            'price' => 100,
            'minimum_order' => 10,
            'maximum_order' => 5,
        ])
        ->assertSessionHasErrors('maximum_order');
});

test('inactive variants are excluded from public product page', function () {
    $product = createVariantProduct();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => '১ কেজি',
        'is_active' => true,
    ]);
    ProductVariant::factory()->inactive()->create([
        'product_id' => $product->id,
        'name' => '৫ কেজি',
        'is_active' => false,
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('১ কেজি')
        ->assertDontSee('৫ কেজি');
});

test('active product with variants shows variant selection', function () {
    $product = createVariantProduct();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => '১ কেজি',
        'price' => 160,
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('প্যাকেজ নির্বাচন করুন')
        ->assertSee('১ কেজি');
});

test('admin can update a variant', function () {
    $product = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs(variantAdminUser())
        ->put(route('admin.products.variants.update', [$product, $variant]), [
            'name' => '৫ কেজি',
            'sku' => $variant->sku,
            'price' => 750,
        ])
        ->assertRedirect(route('admin.products.variants.index', $product));

    expect($variant->fresh()->name)->toBe('৫ কেজি');
});

test('admin can delete a variant', function () {
    $product = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs(variantAdminUser())
        ->delete(route('admin.products.variants.destroy', [$product, $variant]))
        ->assertRedirect(route('admin.products.variants.index', $product));

    expect(ProductVariant::find($variant->id))->toBeNull();
});

test('wrong product variant ownership is blocked', function () {
    $product1 = createVariantProduct();
    $product2 = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product2->id]);

    $this->actingAs(variantAdminUser())
        ->get(route('admin.products.variants.edit', [$product1, $variant]))
        ->assertNotFound();
});

test('wrong product variant update is blocked', function () {
    $product1 = createVariantProduct();
    $product2 = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product2->id]);

    $this->actingAs(variantAdminUser())
        ->put(route('admin.products.variants.update', [$product1, $variant]), [
            'name' => 'হ্যাকড',
            'sku' => $variant->sku,
            'price' => 1,
        ])
        ->assertNotFound();
});

test('wrong product variant delete is blocked', function () {
    $product1 = createVariantProduct();
    $product2 = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product2->id]);

    $this->actingAs(variantAdminUser())
        ->delete(route('admin.products.variants.destroy', [$product1, $variant]))
        ->assertNotFound();
});

test('variant has discount helper', function () {
    $variant = ProductVariant::factory()->create(['price' => 100, 'discount_price' => 80]);
    expect($variant->hasDiscount())->toBeTrue();

    $variant2 = ProductVariant::factory()->create(['price' => 100, 'discount_price' => null]);
    expect($variant2->hasDiscount())->toBeFalse();
});

test('variant effective price attribute works', function () {
    $variant = ProductVariant::factory()->create(['price' => 100, 'discount_price' => 80]);
    expect($variant->effective_price)->toBe('80.00');

    $variant2 = ProductVariant::factory()->create(['price' => 100, 'discount_price' => null]);
    expect($variant2->effective_price)->toBe('100.00');
});

test('product variants are ordered by weight then name', function () {
    $product = createVariantProduct();
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => '১০ কেজি', 'weight' => 10]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => '১ কেজি', 'weight' => 1]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => '৫ কেজি', 'weight' => 5]);

    $variants = $product->variants()->ordered()->get();

    expect($variants->pluck('weight')->toArray())->toBe(['1.00', '5.00', '10.00']);
});

test('product with variants shows price range on customer page', function () {
    $product = createVariantProduct();
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => '১ কেজি', 'price' => 160]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => '৫ কেজি', 'price' => 750]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('160.00')
        ->assertSee('750.00');
});

test('admin can view variant index page', function () {
    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->get(route('admin.products.variants.index', $product))
        ->assertOk()
        ->assertSee('ভ্যারিয়েন্ট ব্যবস্থাপনা');
});

test('admin can view variant create page', function () {
    $product = createVariantProduct();

    $this->actingAs(variantAdminUser())
        ->get(route('admin.products.variants.create', $product))
        ->assertOk()
        ->assertSee('নতুন ভ্যারিয়েন্ট যোগ করুন');
});

test('admin can view variant edit page', function () {
    $product = createVariantProduct();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs(variantAdminUser())
        ->get(route('admin.products.variants.edit', [$product, $variant]))
        ->assertOk()
        ->assertSee('ভ্যারিয়েন্ট সম্পাদনা করুন');
});
