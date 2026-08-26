<?php

namespace App\Data;

use App\Enums\DeliveryMethod;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * চেকআউটের server-side হিসাব — Blade-এ raw model নয়, গঠিত ডেটা।
 * সব অঙ্ক server-এই হয়; browser-এর subtotal/fee/total কখনোই trusted নয়।
 */
readonly class CheckoutSummary
{
    /**
     * @param  array<int, array{variant: ProductVariant, product: Product, quantity: int, unit_price: float, line_total: float}>  $items
     */
    public function __construct(
        public array $items,
        public float $subtotal,
        public float $deliveryFee,
        public float $grandTotal,
        public DeliveryMethod $deliveryMethod,
        public ?Address $address = null,
        public array $notices = [],
    ) {}
}
