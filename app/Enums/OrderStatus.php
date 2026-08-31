<?php

namespace App\Enums;

/**
 * অর্ডারের অবস্থা — অ্যাডমিন-ব্যবস্থাপনাযোগ্য state machine।
 * বিদ্যমান Order::STATUS_PENDING-এর সাথে সামঞ্জস্যপূর্ণ মান ব্যবহার করে।
 */
enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

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
        return __('admin.orders.status.'.$this->value);
    }

    /**
     * অ্যাডমিন UI-তে ব্যবহৃত রঙের শ্রেণি
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'text-bg-warning',
            self::CONFIRMED => 'text-bg-primary',
            self::PROCESSING => 'text-bg-info',
            self::SHIPPED => 'text-bg-secondary',
            self::COMPLETED => 'text-bg-success',
            self::CANCELLED => 'text-bg-danger',
            self::RETURNED => 'text-bg-dark',
        };
    }
}
