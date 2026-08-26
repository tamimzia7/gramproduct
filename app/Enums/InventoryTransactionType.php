<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN_STOCK = 'return';
    case ADJUSTMENT = 'adjustment';
    case DAMAGE = 'damage';
    case EXPIRED = 'expired';
    case RESTOCK = 'restock';
    case RESERVATION = 'reservation';
    case RESERVATION_RELEASE = 'reservation_release';

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
     * স্টক বাড়ায় এমন লেনদেন কি না
     */
    public function increasesStock(): bool
    {
        return in_array($this, [self::PURCHASE, self::RETURN_STOCK, self::RESTOCK], true);
    }

    /**
     * অ্যাডমিন-ফেসিং বাংলা লেবেল (lang/bn/inventory.php থেকে)
     */
    public function label(): string
    {
        return __('inventory.types.'.$this->value);
    }
}
