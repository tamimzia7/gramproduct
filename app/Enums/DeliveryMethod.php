<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case HOME_DELIVERY = 'home_delivery';

    /**
     * সব মেথডের মান — validation-এর জন্য
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return __('checkout.delivery.'.$this->value);
    }

    /**
     * ডেলিভারি ফি — config/delivery.php থেকে; Blade/controller-এ hard-code নয়
     */
    public function fee(): float
    {
        return (float) config('delivery.fees.'.$this->value, config('delivery.default_fee'));
    }
}
