<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function adminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('product-manager');

    return $user;
}

test('guest is redirected from admin category pages', function () {
    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    $this->get(route('admin.categories.create'))->assertRedirect(route('login'));
});

test('non permitted user cannot access admin categories', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('admin can create a top level category', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.categories.store'), [
            'name' => 'চাল',
            'slug' => 'rice',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::where('slug', 'rice')->exists())->toBeTrue();
});

test('category supports parent child hierarchy', function () {
    $parent = Category::factory()->create(['name' => 'চাল', 'slug' => 'rice']);
    $child = Category::factory()->create(['name' => 'নাজিরশাইল', 'slug' => 'nazirshail', 'parent_id' => $parent->id]);

    expect($child->parent->is($parent))->toBeTrue();
    expect($parent->children()->first()->is($child))->toBeTrue();
});

test('category supports deeper recursion', function () {
    $top = Category::factory()->create(['slug' => 'rice']);
    $mid = Category::factory()->create(['slug' => 'local-rice', 'parent_id' => $top->id]);
    $leaf = Category::factory()->create(['slug' => 'premium-local-rice', 'parent_id' => $mid->id]);

    expect($leaf->getAllDescendantIds())->toBe([]);
    expect($top->getAllDescendantIds())->toContain($mid->id, $leaf->id);
});

test('duplicate slug is rejected', function () {
    Category::factory()->create(['slug' => 'rice']);

    $this->actingAs(adminUser())
        ->post(route('admin.categories.store'), [
            'name' => 'চাল ২',
            'slug' => 'rice',
        ])
        ->assertSessionHasErrors('slug');
});

test('self parenting is rejected on update', function () {
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'parent_id' => $category->id,
        ])
        ->assertSessionHasErrors('parent_id');

    expect($category->fresh()->parent_id)->toBeNull();
});

test('circular hierarchy is rejected on update', function () {
    $parent = Category::factory()->create(['slug' => 'rice']);
    $child = Category::factory()->create(['slug' => 'nazirshail', 'parent_id' => $parent->id]);

    $this->actingAs(adminUser())
        ->put(route('admin.categories.update', $parent), [
            'name' => $parent->name,
            'parent_id' => $child->id,
        ])
        ->assertSessionHasErrors('parent_id');

    expect($parent->fresh()->parent_id)->toBeNull();
});

test('inactive categories are excluded from public browsing', function () {
    Category::factory()->create(['slug' => 'active-cat', 'is_active' => true]);
    Category::factory()->create(['slug' => 'inactive-cat', 'is_active' => false]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('active-cat')
        ->assertDontSee('inactive-cat');
});

test('inactive category is not viewable publicly', function () {
    $category = Category::factory()->create(['slug' => 'hidden', 'is_active' => false]);

    $this->get(route('categories.show', $category->slug))->assertNotFound();
});

test('active category is viewable publicly', function () {
    $category = Category::factory()->create(['slug' => 'visible', 'is_active' => true]);

    $this->get(route('categories.show', $category->slug))->assertOk()->assertSee($category->name);
});

test('category with subcategories cannot be deleted', function () {
    $parent = Category::factory()->create();
    Category::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs(adminUser())
        ->delete(route('admin.categories.destroy', $parent))
        ->assertRedirect(route('admin.categories.index'));

    expect($parent->fresh()->trashed())->toBeFalse();
});

test('leaf category can be soft deleted', function () {
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    expect($category->fresh()->trashed())->toBeTrue();
});
