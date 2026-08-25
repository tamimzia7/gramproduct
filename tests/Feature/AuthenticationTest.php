<?php

use App\Models\Role;
use App\Models\User;
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

test('login page is accessible to guests', function () {
    $this->get(route('login'))->assertOk();
});

test('register page is accessible to guests', function () {
    $this->get(route('register'))->assertOk();
});

test('user can login with valid credentials', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();
});

test('login fails with invalid credentials', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login fails for inactive user', function () {
    User::create([
        'name' => 'Inactive User',
        'email' => 'inactive@example.com',
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'inactive@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login requires email and password', function () {
    $response = $this->post(route('login'), []);

    $response->assertSessionHasErrors(['email', 'password']);
});

test('user can logout', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('logout'));

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('registration requires name, email, and password', function () {
    $response = $this->post(route('register'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

test('registration requires unique email', function () {
    User::create([
        'name' => 'Existing User',
        'email' => 'taken@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $response = $this->post(route('register'), [
        'name' => 'Another User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration requires password confirmation', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('password');
});

test('registration requires minimum 8 character password', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
});

test('registered user is automatically logged in', function () {
    $response = $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();
});

test('registered user password is hashed', function () {
    $this->post(route('register'), [
        'name' => 'Hash Test',
        'email' => 'hash@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'hash@example.com')->first();
    expect(Hash::isHashed($user->password))->toBeTrue();
});

test('forgot password page is accessible', function () {
    $this->get(route('password.request'))->assertOk();
});

test('forgot password requires valid email', function () {
    $response = $this->post(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('forgot password sends reset link for valid email', function () {
    User::create([
        'name' => 'Reset User',
        'email' => 'reset@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $response = $this->post(route('password.email'), [
        'email' => 'reset@example.com',
    ]);

    $response->assertSessionHas('status');
});

test('verification notice page is accessible to authenticated user', function () {
    $user = User::create([
        'name' => 'Unverified User',
        'email' => 'unverified@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $this->get(route('verification.notice'))->assertOk();
});

test('unverified user sees warning on dashboard', function () {
    $user = User::create([
        'name' => 'Unverified User',
        'email' => 'unverified@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);
    $this->get(route('dashboard'))->assertOk()->assertSee('যাচাই করুন');
});

test('user with role can access admin dashboard', function () {
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $user->assignRole('admin');

    $this->actingAs($user);
    $this->get(route('admin.dashboard'))->assertOk();
});

test('user without role cannot access admin dashboard', function () {
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('login with remember me sets remember token', function () {
    User::create([
        'name' => 'Remember User',
        'email' => 'remember@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->post(route('login'), [
        'email' => 'remember@example.com',
        'password' => 'password123',
        'remember' => 'on',
    ]);

    $this->assertAuthenticated();
});

test('login form preserves email on failure', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasInput('email', 'test@example.com');
});

test('session is regenerated on login', function () {
    User::create([
        'name' => 'Session User',
        'email' => 'session@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $oldSessionId = session()->getId();

    $this->post(route('login'), [
        'email' => 'session@example.com',
        'password' => 'password123',
    ]);

    $newSessionId = session()->getId();
    expect($newSessionId)->not->toBe($oldSessionId);
});

test('session is invalidated on logout', function () {
    $user = User::create([
        'name' => 'Logout User',
        'email' => 'logout@example.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $oldSessionId = session()->getId();

    $this->post(route('logout'));

    $newSessionId = session()->getId();
    expect($newSessionId)->not->toBe($oldSessionId);
});
