<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
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

    public function test_super_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('ড্যাশবোর্ড');
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect();
    }

    public function test_non_admin_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_inactive_admin_is_blocked(): void
    {
        $user = $this->createAdmin();
        $user->forceFill(['is_active' => false])->save();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }
}
