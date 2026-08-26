<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Models\User;

class DeliveryService
{
    /**
     * উপলব্ধ ডেলিভারি মেথডসমূহ
     *
     * @return array<int, DeliveryMethod>
     */
    public function methods(): array
    {
        return array_map(
            fn (string $value) => DeliveryMethod::from($value),
            DeliveryMethod::values(),
        );
    }

    public function defaultMethod(): DeliveryMethod
    {
        return DeliveryMethod::from(config('delivery.default_method'));
    }

    public function isValidMethod(string $method): bool
    {
        return in_array($method, DeliveryMethod::values(), true);
    }

    /**
     * ডেলিভারি ফি — শুধুমাত্র server-side হিসাবের জন্য
     */
    public function fee(DeliveryMethod $method, ?User $user = null): float
    {
        return $method->fee();
    }
}
