<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('roles are seeded', function () {
    expect(Role::count())->toBe(7);
    expect(Role::where('slug', 'super-admin')->exists())->toBeTrue();
});

test('guest is redirected away from dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('registration creates a customer without a role', function () {
    $response = $this->post(route('register'), [
        'name' => 'Jane Customer',
        'email' => 'jane@example.com',
        'phone' => '01700000000',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('home'));

    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasAnyRole())->toBeFalse();
    expect($user->isActive())->toBeTrue();
});

test('a seeded staff role can be assigned and verified', function () {
    $user = User::factory()->create();
    $user->assignRole('product-manager');

    expect($user->hasRole('product-manager'))->toBeTrue();
    expect($user->hasPermission('manage-products'))->toBeTrue();
    expect($user->hasPermission('manage-orders'))->toBeFalse();
});

function configureAdmin(array $overrides = []): array
{
    $credentials = array_merge([
        'name' => 'প্রশাসক',
        'email' => 'admin@example.test',
        'password' => 'very-secret-pass',
    ], $overrides);

    config([
        'shop.admin.name' => $credentials['name'],
        'shop.admin.email' => $credentials['email'],
        'shop.admin.password' => $credentials['password'],
        'shop.admin.role' => 'super-admin',
    ]);

    return $credentials;
}

test('admin login page loads with Bengali-first labels', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('ইমেইল ঠিকানা')
        ->assertSee('পাসওয়ার্ড')
        ->assertSee('লগইন করুন')
        ->assertSee('পাসওয়ার্ড ভুলে গেছেন?')
        ->assertSee('name="_token"', false);
});

test('admin seeder creates a hashed, role-assigned admin without duplicates', function () {
    $credentials = configureAdmin();

    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', $credentials['email'])->first();

    expect($admin)->not->toBeNull();
    expect($admin->name)->toBe('প্রশাসক');
    expect(Hash::check($credentials['password'], $admin->password))->toBeTrue();
    expect($admin->hasRole('super-admin'))->toBeTrue();
    expect($admin->isActive())->toBeTrue();

    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', $credentials['email'])->count())->toBe(1);
});

test('admin seeder does nothing without full credentials', function () {
    config([
        'shop.admin.name' => 'প্রশাসক',
        'shop.admin.email' => 'incomplete@example.com',
        'shop.admin.password' => '',
    ]);

    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', 'incomplete@example.com')->exists())->toBeFalse();
});

test('admin can log in with email and password and reach the dashboard', function () {
    $credentials = configureAdmin();
    $this->seed(AdminUserSeeder::class);

    $sessionBefore = session()->getId();

    $response = $this->post(route('login'), [
        'email' => $credentials['email'],
        'password' => $credentials['password'],
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticated();
    expect(session()->getId())->not->toBe($sessionBefore);

    $this->get(route('admin.dashboard'))->assertOk();
});

test('invalid password fails for an admin account', function () {
    $credentials = configureAdmin();
    $this->seed(AdminUserSeeder::class);

    $this->post(route('login'), [
        'email' => $credentials['email'],
        'password' => 'wrong-password',
    ])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('invalid email fails to log in', function () {
    $this->post(route('login'), [
        'email' => 'nobody@example.com',
        'password' => 'whatever-pass',
    ])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('non-admin cannot access the admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

test('admin logout works and invalidates the session', function () {
    $credentials = configureAdmin();
    $this->seed(AdminUserSeeder::class);
    $this->actingAs(User::where('email', $credentials['email'])->first());

    $this->post(route('logout'))->assertRedirect('/');

    $this->assertGuest();
});
