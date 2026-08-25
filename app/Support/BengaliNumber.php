<?php

namespace App\Support;

/**
 * বাংলা সংখ্যা ফরম্যাটিং — শুধুমাত্র presentation লেভেলে ব্যবহৃত হয়।
 * ডাটাবেসে ক্যানোনিক্যাল (English) সংখ্যাই থাকে।
 */
class BengaliNumber
{
    private const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    /**
     * English ডিজিটকে বাংলা ডিজিটে রূপান্তর
     */
    public static function format(int|float|string|null $value): string
    {
        return str_replace(
            range(0, 9),
            self::DIGITS,
            (string) $value
        );
    }

    /**
     * মূল্য ফরম্যাট — "৳১২০"
     */
    public static function money(int|float|string|null $amount): string
    {
        $formatted = number_format((float) $amount, ((float) $amount == (int) $amount) ? 0 : 2);

        return '৳'.self::format($formatted);
    }

    /**
     * এককসহ মূল্য — "৳১২০ / কেজি"
     */
    public static function priceWithUnit(int|float|string|null $amount, ?string $unitLabel = null): string
    {
        if ($unitLabel === null || $unitLabel === '') {
            return self::money($amount);
        }

        return self::money($amount).' / '.$unitLabel;
    }
}
