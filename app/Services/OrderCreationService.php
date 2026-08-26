<?php

namespace App\Services;

use App\Data\CheckoutSummary;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

/**
 * Phase 09 Order মডিউলের জন্য নিরাপদ সীমানা।
 *
 * অর্ডার pending + unpaid হিসেবেই তৈরি হয়; payment settlement এই ফেজে নয়।
 * Stock সরাসরি কাটা হয় না — InventoryService::reserve() দিয়ে সংরক্ষিত হয়,
 * Phase 09 completion-এ reservation → sale রূপান্তরিত হবে।
 *
 * অবশ্যই কলারের DB::transaction-এর ভেতরে চলবে (inventory rows আগেই locked)।
 */
class OrderCreationService
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    public function createFromCheckout(User $user, CheckoutSummary $summary): Order
    {
        $address = $summary->address;

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => $this->generateOrderNumber(),
            'address_id' => $address?->id,
            'receiver_name' => $address->name,
            'receiver_phone' => $address->phone,
            'division' => $address->division,
            'district' => $address->district,
            'upazila' => $address->upazila,
            'area' => $address->area,
            'address_line' => $address->address_line,
            'postal_code' => $address->postal_code,
            'delivery_note' => $address->delivery_note,
            'delivery_method' => $summary->deliveryMethod->value,
            'subtotal' => $summary->subtotal,
            'delivery_fee' => $summary->deliveryFee,
            'grand_total' => $summary->grandTotal,
            'currency' => 'BDT',
            'payment_method' => Order::PAYMENT_COD, // placeholder — settlement Phase 09+
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        foreach ($summary->items as $item) {
            $this->inventoryService->reserve(
                $item['variant'],
                $item['quantity'],
                ['type' => 'order', 'id' => $order->id],
                __('checkout.notes.reserved'),
            );

            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $item['variant']->id,
                'product_name' => $item['product']->name,
                'variant_name' => $item['variant']->name,
                'variant_sku' => $item['variant']->sku,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
            ]);
        }

        return $order->load('items');
    }

    /**
     * পাঠযোগ্য বাংলা-বান্ধব Latin order number — ORD-YYYYMMDD-XXXXX
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
