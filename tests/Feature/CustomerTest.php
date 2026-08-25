<?php

use App\Models\Address;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('guest is redirected from customer profile', function () {
    $this->get(route('customer.profile'))->assertRedirect(route('login'));
});

test('authenticated user can view profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.profile'))->assertOk();
});

test('profile shows user information', function () {
    $user = User::factory()->create([
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'phone' => '01700000000',
    ]);

    $this->actingAs($user);
    $this->get(route('customer.profile'))
        ->assertSee('Test Customer')
        ->assertSee('test@example.com')
        ->assertSee('01700000000');
});

test('user can update profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->put(route('customer.profile.update'), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '01800000000',
    ])->assertRedirect(route('customer.profile'));

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->phone)->toBe('01800000000');
});

test('profile update requires name and email', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->put(route('customer.profile.update'), [])
        ->assertSessionHasErrors(['name', 'email']);
});

test('profile update requires unique email', function () {
    $user = User::factory()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($user);
    $this->put(route('customer.profile.update'), [
        'name' => 'Test',
        'email' => 'taken@example.com',
    ])->assertSessionHasErrors('email');
});

test('user can view settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.settings'))->assertOk();
});

test('user can change password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->actingAs($user);
    $this->put(route('customer.password.update'), [
        'current_password' => 'old-password',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertRedirect(route('customer.settings'));

    $user->refresh();
    expect(Hash::check('new-password123', $user->password))->toBeTrue();
});

test('password change requires current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->put(route('customer.password.update'), [
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertSessionHasErrors('current_password');
});

test('password change requires matching confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->actingAs($user);
    $this->put(route('customer.password.update'), [
        'current_password' => 'old-password',
        'password' => 'new-password123',
        'password_confirmation' => 'different-password',
    ])->assertSessionHasErrors('password');
});

test('guest is redirected from addresses', function () {
    $this->get(route('customer.addresses.index'))->assertRedirect(route('login'));
});

test('user can view addresses index', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.addresses.index'))->assertOk();
});

test('addresses index shows empty state', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.addresses.index'))
        ->assertSee('কোনো ঠিকানা নেই');
});

test('user can view create address form', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.addresses.create'))->assertOk();
});

test('user can store address', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->post(route('customer.addresses.store'), [
        'name' => 'Test User',
        'type' => 'shipping',
        'address_line_1' => '123 Main Street',
        'city' => 'Dhaka',
        'country' => 'Bangladesh',
    ])->assertRedirect(route('customer.addresses.index'));

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'name' => 'Test User',
        'city' => 'Dhaka',
    ]);
});

test('address store requires name, type, address_line_1, city, country', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->post(route('customer.addresses.store'), [])
        ->assertSessionHasErrors(['name', 'type', 'address_line_1', 'city', 'country']);
});

test('address store validates type enum', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->post(route('customer.addresses.store'), [
        'name' => 'Test',
        'type' => 'invalid',
        'address_line_1' => '123 Main St',
        'city' => 'Dhaka',
        'country' => 'Bangladesh',
    ])->assertSessionHasErrors('type');
});

test('user can edit address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);
    $this->get(route('customer.addresses.edit', $address))->assertOk();
});

test('user cannot edit another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user);
    $this->get(route('customer.addresses.edit', $address))->assertForbidden();
});

test('user can update address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id, 'city' => 'Old City']);

    $this->actingAs($user);
    $this->put(route('customer.addresses.update', $address), [
        'name' => $address->name,
        'type' => $address->type,
        'address_line_1' => $address->address_line_1,
        'city' => 'New City',
        'country' => 'Bangladesh',
    ])->assertRedirect(route('customer.addresses.index'));

    $address->refresh();
    expect($address->city)->toBe('New City');
});

test('user cannot update another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user);
    $this->put(route('customer.addresses.update', $address), [
        'name' => $address->name,
        'type' => $address->type,
        'address_line_1' => $address->address_line_1,
        'city' => 'Hacked',
        'country' => 'Bangladesh',
    ])->assertForbidden();
});

test('user can delete address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);
    $this->delete(route('customer.addresses.destroy', $address))
        ->assertRedirect(route('customer.addresses.index'));

    $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
});

test('user cannot delete another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user);
    $this->delete(route('customer.addresses.destroy', $address))->assertForbidden();
});

test('user can set address as default', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    $this->actingAs($user);
    $this->put(route('customer.addresses.update', $address), [
        'name' => $address->name,
        'type' => $address->type,
        'address_line_1' => $address->address_line_1,
        'city' => $address->city,
        'country' => 'Bangladesh',
        'is_default' => true,
    ]);

    $address->refresh();
    expect($address->is_default)->toBeTrue();
});

test('setting default clears previous default of same type', function () {
    $user = User::factory()->create();
    $oldDefault = Address::factory()->shipping()->default()->create(['user_id' => $user->id]);
    $newAddress = Address::factory()->shipping()->create(['user_id' => $user->id]);

    $newAddress->setAsDefault();

    $oldDefault->refresh();
    expect($oldDefault->is_default)->toBeFalse();
    expect($newAddress->fresh()->is_default)->toBeTrue();
});

test('address type is stored correctly', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->post(route('customer.addresses.store'), [
        'name' => 'Test',
        'type' => 'billing',
        'address_line_1' => '123 Main St',
        'city' => 'Dhaka',
        'country' => 'Bangladesh',
    ]);

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'type' => 'billing',
    ]);
});

test('user addresses relationship works', function () {
    $user = User::factory()->create();
    Address::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->addresses)->toHaveCount(3);
});

test('user can view order history page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.order-history'))->assertOk();
});

test('order history shows placeholder message', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->get(route('customer.order-history'))
        ->assertSee('শীঘ্রই যুক্ত হবে');
});

test('customer routes require authentication', function () {
    $routes = [
        route('customer.profile'),
        route('customer.settings'),
        route('customer.addresses.index'),
        route('customer.addresses.create'),
        route('customer.order-history'),
    ];

    foreach ($routes as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});
