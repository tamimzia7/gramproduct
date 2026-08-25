<?php

namespace App\Enums;

enum ProductUnit: string
{
    case KG = 'kg';
    case GRAM = 'gram';
    case LITER = 'liter';
    case ML = 'ml';
    case PIECE = 'piece';
    case PACK = 'pack';
    case BOTTLE = 'bottle';
    case BAG = 'bag';

    /**
     * সব কেসের মান — validation-এর জন্য
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * কাস্টমার-ফেসিং বাংলা লেবেল (lang/bn/product.php থেকে)
     */
    public function label(): string
    {
        return __('product.units.'.$this->value);
    }
}
