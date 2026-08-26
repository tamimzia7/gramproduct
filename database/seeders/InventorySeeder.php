<?php

namespace Database\Seeders;

use App\Enums\InventoryTransactionType;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * ডেমো ইনভেন্টরি — প্রতিটি ভ্যারিয়েন্টের স্টক + প্রাথমিক লেনদেন (অডিট প্রদর্শনের জন্য)।
     * কিছু ইচ্ছাকৃতভাবে low-stock / out-of-stock — ড্যাশবোর্ড স্ট্যাটস যাচাইয়ের জন্য।
     */
    public function run(): void
    {
        $service = app(InventoryService::class);

        // SKU => [quantity, low_stock_threshold]
        $plan = [
            // নাজিরশাইল চাল
            'RICE-NS-1KG' => [120, 25],
            'RICE-NS-5KG' => [70, 10],
            'RICE-NS-10KG' => [35, 8],
            // দেশি কৈ মাছ
            'FISH-KOI-500G' => [18, 10],
            'FISH-KOI-1KG' => [26, 10],
            'FISH-KOI-2KG' => [7, 10],
            // খাঁটি সরিষার তেল
            'OIL-MST-250ML' => [45, 12],
            'OIL-MST-500ML' => [9, 12],
        ];

        foreach ($plan as $sku => [$quantity, $threshold]) {
            $variant = ProductVariant::where('sku', $sku)->first();

            if (! $variant) {
                continue;
            }

            $inventory = $service->ensureInventory($variant, ['low_stock_threshold' => $threshold]);

            // idempotent — আগে কোনো লেনদেন থাকলে আবার স্টক যোগ হবে না
            if ($quantity > 0 && ! $inventory->transactions()->exists()) {
                $service->addStock(
                    $variant,
                    $quantity,
                    'প্রাথমিক স্টক (সিড)',
                    null,
                    InventoryTransactionType::PURCHASE,
                );
            }
        }

        // স্টক-শেষ ডেমো — ১ লিটার সরিষার তেল
        $outVariant = ProductVariant::where('sku', 'OIL-MST-1LTR')->first();

        if ($outVariant) {
            $service->ensureInventory($outVariant, ['low_stock_threshold' => 6]);
        }
    }
}
