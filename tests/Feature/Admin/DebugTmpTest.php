<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugTmpTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_inventory_index(): void
    {
        Role::updateOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin', 'permissions' => []]);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');

        $resp = $this->actingAs($admin)->get(route('admin.inventory.index'));
        fwrite(STDERR, "\nSTATUS=" . $resp->getStatusCode() . "\n");
        if ($resp->getStatusCode() !== 200) {
            fwrite(STDERR, "BODY:\n" . $resp->getContent() . "\n");
        }
        $resp->assertOk();
    }

    public function test_debug_storefront_product(): void
    {
        $category = \App\Models\Category::factory()->create();
        $product = \App\Models\Product::factory()->create(['category_id' => $category->id]);
        $resp = $this->get(route('products.show', $product));
        fwrite(STDERR, "\nSTATUS2=" . $resp->getStatusCode() . "\n");
        if ($resp->getStatusCode() !== 200) {
            fwrite(STDERR, "BODY2:\n" . $resp->getContent() . "\n");
        }
        $resp->assertOk();
    }
}
