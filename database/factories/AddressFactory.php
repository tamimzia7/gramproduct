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
            'name' => 'তামিম',
            'phone' => '01712345678',
            'division' => 'ঢাকা',
            'district' => 'গাজীপুর',
            'upazila' => 'কালীগঞ্জ',
            'area' => 'উত্তরা',
            'address_line' => 'বাসা ১২, রোড ৩',
            'postal_code' => '1700',
            'delivery_note' => null,
            'is_default' => false,
        ];
    }

    /**
     * ডিফল্ট ঠিকানা state
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
