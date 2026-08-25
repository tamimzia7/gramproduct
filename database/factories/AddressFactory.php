<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['বাসা', 'অফিস', 'কাজের ঠিকানা']),
            'type' => fake()->randomElement(['billing', 'shipping']),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address_line_1' => fake()->address(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Bangladesh',
            'is_default' => false,
        ];
    }

    public function billing(): static
    {
        return $this->state(fn () => ['type' => 'billing']);
    }

    public function shipping(): static
    {
        return $this->state(fn () => ['type' => 'shipping']);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
