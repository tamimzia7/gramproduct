<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileTest extends TestCase
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

    public function test_admin_can_view_profile(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');

        $this->actingAs($user)->get(route('admin.profile.index'))
            ->assertOk()
            ->assertSee('আমার প্রোফাইল');
    }

    public function test_admin_can_update_profile(): void
    {
        $user = User::factory()->create(['is_active' => true, 'name' => 'Old Name']);
        $user->assignRole('super-admin');

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_admin_can_change_password(): void
    {
        $user = User::factory()->create(['is_active' => true, 'password' => 'old-password']);
        $user->assignRole('super-admin');

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password', $user->fresh()->password));
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $this->get(route('admin.profile.index'))->assertRedirect();
    }
}
