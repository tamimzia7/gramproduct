<?php

namespace App\Enums;

/**
 * অর্ডারের পেমেন্ট অবস্থা।
 * বিদ্যমান Order::PAYMENT_UNPAID-এর সাথে সামঞ্জস্যপূর্ণ মান ব্যবহার করে।
 */
enum OrderPaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case PARTIALLY_PAID = 'partially_paid';
    case FAILED = 'failed';

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
     * অ্যাডমিন-ফেসিং বাংলা লেবেল
     */
    public function label(): string
    {
        return __('admin.orders.payment_status.'.$this->value);
    }

    /**
     * অ্যাডমিন UI-তে ব্যবহৃত রঙের শ্রেণি
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PAID => 'text-bg-success',
            self::UNPAID => 'text-bg-warning',
            self::REFUNDED, self::PARTIALLY_PAID => 'text-bg-info',
            self::FAILED => 'text-bg-danger',
        };
    }
}
