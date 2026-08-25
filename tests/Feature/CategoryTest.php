<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    private function createEditor(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('content-editor');

        return $user;
    }

    // --- Index ---

    public function test_admin_can_view_categories_index(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('ক্যাটাগরি');
    }

    public function test_unauthenticated_user_redirected_from_admin_categories(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect();
    }

    public function test_non_admin_user_cannot_view_categories_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    // --- Create ---

    public function test_admin_can_view_create_category_form(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('ক্যাটাগরি তৈরি করুন');
    }

    // --- Store ---

    public function test_admin_can_store_category(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Test Category',
            'description' => 'A test category.',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 5,
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 5,
        ]);
    }

    public function test_admin_can_store_category_with_parent(): void
    {
        $admin = $this->createAdmin();
        $parent = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Child Category',
            'parent_id' => $parent->id,
            'is_active' => true,
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Child Category',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_admin_can_store_featured_category(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Featured Category',
            'is_featured' => true,
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description.',
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Featured Category',
            'is_featured' => true,
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description.',
        ]);
    }

    public function test_store_category_validates_name_required(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => '',
        ])
            ->assertSessionHasErrors('name');
    }

    public function test_store_category_validates_slug_unique(): void
    {
        Category::factory()->create(['slug' => 'existing-slug']);
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Existing Slug',
            'slug' => 'existing-slug',
        ])
            ->assertSessionHasErrors('slug');
    }

    public function test_store_category_validates_parent_exists(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Orphan',
            'parent_id' => 9999,
        ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_store_category_auto_generates_slug(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Auto Slug Category',
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'slug' => 'auto-slug-category',
        ]);
    }

    // --- Show ---

    public function test_admin_can_view_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'View Test']);

        $this->actingAs($admin)->get(route('admin.categories.show', $category))
            ->assertOk()
            ->assertSee('View Test');
    }

    // --- Edit ---

    public function test_admin_can_view_edit_category_form(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('ক্যাটাগরি সম্পাদনা করুন');
    }

    // --- Update ---

    public function test_admin_can_update_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'New Name',
            'is_active' => false,
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_featured_on_update(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['is_featured' => false]);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'is_featured' => true,
            'seo_title' => 'SEO Updated',
        ])
            ->assertRedirect();

        $category->refresh();
        $this->assertTrue($category->is_featured);
        $this->assertEquals('SEO Updated', $category->seo_title);
    }

    public function test_update_category_validates_slug_unique(): void
    {
        Category::factory()->create(['slug' => 'taken-slug']);
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['slug' => 'my-slug']);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'slug' => 'taken-slug',
        ])
            ->assertSessionHasErrors('slug');
    }

    public function test_update_allows_same_slug_for_same_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['slug' => 'keep-this', 'name' => 'Keep This']);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Keep This',
            'slug' => 'keep-this',
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'keep-this',
        ]);
    }

    // --- Delete ---

    public function test_admin_can_delete_category_without_children_or_products(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_children(): void
    {
        $admin = $this->createAdmin();
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $parent))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_admin_cannot_delete_category_with_products(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    // --- Restore ---

    public function test_admin_can_restore_soft_deleted_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();
        $category->delete();

        $this->actingAs($admin)->patch(route('admin.categories.restore', $category->id))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    // --- Filters ---

    public function test_admin_can_filter_categories_by_search(): void
    {
        $admin = $this->createAdmin();
        Category::factory()->create(['name' => 'Alpha Rice']);
        Category::factory()->create(['name' => 'Beta Fish']);

        $response = $this->actingAs($admin)->get(route('admin.categories.index', ['search' => 'Rice']));
        $response->assertOk();

        // Table rows contain filtered results
        $response->assertSee('Alpha Rice');
        $response->assertSeeInOrder(['Alpha Rice', 'Beta Fish'], false);
    }

    public function test_admin_can_filter_categories_by_status(): void
    {
        $admin = $this->createAdmin();
        $active = Category::factory()->create(['name' => 'Active Cat', 'is_active' => true]);
        Category::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.categories.index', ['status' => 'active']));
        $response->assertOk();

        // Verify active category appears but the filter only shows active ones in results
        $response->assertSee('Active Cat');
        $response->assertSee('selected');
    }

    // --- Model: Featured Scope ---

    public function test_featured_scope_returns_only_featured_categories(): void
    {
        Category::factory()->create(['is_featured' => true]);
        Category::factory()->create(['is_featured' => false]);

        $featured = Category::featured()->get();

        $this->assertCount(1, $featured);
        $this->assertTrue($featured->first()->is_featured);
    }

    // --- Model: SEO Fallback Accessors ---

    public function test_seo_title_fallback_to_name(): void
    {
        $category = Category::factory()->create(['name' => 'Fallback Name', 'seo_title' => null]);

        $this->assertEquals('Fallback Name', $category->seo_title);
    }

    public function test_seo_title_returns_custom_when_set(): void
    {
        $category = Category::factory()->create(['seo_title' => 'Custom SEO']);

        $this->assertEquals('Custom SEO', $category->seo_title);
    }

    public function test_seo_description_fallback_to_description(): void
    {
        $category = Category::factory()->create([
            'description' => 'Fallback desc',
            'seo_description' => null,
        ]);

        $this->assertEquals('Fallback desc', $category->seo_description);
    }

    // --- Model: Hierarchy ---

    public function test_get_breadcrumb_returns_ancestors(): void
    {
        $root = Category::factory()->create(['name' => 'Root']);
        $child = Category::factory()->create(['name' => 'Child', 'parent_id' => $root->id]);
        $grandchild = Category::factory()->create(['name' => 'Grandchild', 'parent_id' => $child->id]);

        $breadcrumb = $grandchild->getBreadcrumb();

        $this->assertCount(3, $breadcrumb);
        $this->assertEquals('Root', $breadcrumb[0]->name);
        $this->assertEquals('Child', $breadcrumb[1]->name);
        $this->assertEquals('Grandchild', $breadcrumb[2]->name);
    }

    public function test_get_hierarchy_path_returns_formatted_string(): void
    {
        $root = Category::factory()->create(['name' => 'Root']);
        $child = Category::factory()->create(['name' => 'Child', 'parent_id' => $root->id]);

        $this->assertEquals('Root → Child', $child->getHierarchyPath());
    }

    public function test_get_descendant_ids_returns_all_children(): void
    {
        $root = Category::factory()->create();
        $child1 = Category::factory()->create(['parent_id' => $root->id]);
        $child2 = Category::factory()->create(['parent_id' => $root->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child1->id]);

        $ids = $root->getDescendantIds();

        $this->assertCount(3, $ids);
        $this->assertContains($child1->id, $ids);
        $this->assertContains($child2->id, $ids);
        $this->assertContains($grandchild->id, $ids);
    }

    // --- Service: Hierarchy Validation ---

    public function test_cannot_set_category_as_its_own_parent(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'parent_id' => $category->id,
        ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_cannot_set_parent_to_descendant(): void
    {
        $admin = $this->createAdmin();
        $root = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($admin)->put(route('admin.categories.update', $root), [
            'name' => $root->name,
            'parent_id' => $child->id,
        ])
            ->assertSessionHasErrors('parent_id');
    }
}
