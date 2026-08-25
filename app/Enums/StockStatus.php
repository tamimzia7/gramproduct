<?php

namespace App\Enums;

enum StockStatus: string
{
    case IN_STOCK = 'in_stock';
    case OUT_OF_STOCK = 'out_of_stock';

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
        return __('product.stock.'.$this->value);
    }
}
