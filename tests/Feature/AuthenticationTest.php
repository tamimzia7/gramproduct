<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
