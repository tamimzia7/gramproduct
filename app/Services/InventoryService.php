<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function getOrCreateInventory(Product $product, ?ProductVariant $variant = null): Inventory
    {
        return Inventory::firstOrCreate(
            [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'damaged_quantity' => 0,
                'wasted_quantity' => 0,
                'low_stock_threshold' => 10,
                'is_in_stock' => false,
            ]
        );
    }

    public function stockIn(Product $product, int $quantity, ?string $reason = null, ?User $user = null, ?ProductVariant $variant = null): Inventory
    {
        return DB::transaction(function () use ($product, $quantity, $reason, $user, $variant) {
            $inventory = $this->getOrCreateInventory($product, $variant);
            $previousQuantity = $inventory->quantity;

            $inventory->update([
                'quantity' => $inventory->quantity + $quantity,
                'is_in_stock' => true,
            ]);

            $inventory->adjustments()->create([
                'type' => StockAdjustment::TYPE_STOCK_IN,
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $inventory->quantity,
                'reason' => $reason,
                'user_id' => $user?->id,
            ]);

            return $inventory->fresh();
        });
    }

    public function stockOut(Product $product, int $quantity, ?string $reason = null, ?User $user = null, ?ProductVariant $variant = null): Inventory
    {
        return DB::transaction(function () use ($product, $quantity, $reason, $user, $variant) {
            $inventory = $this->getOrCreateInventory($product, $variant);

            if ($inventory->available_quantity < $quantity) {
                throw new \RuntimeException('পর্যাপ্ত মজুদ নেই।');
            }

            $previousQuantity = $inventory->quantity;

            $inventory->update([
                'quantity' => $inventory->quantity - $quantity,
                'is_in_stock' => $inventory->quantity - $quantity > 0,
            ]);

            $inventory->adjustments()->create([
                'type' => StockAdjustment::TYPE_STOCK_OUT,
                'quantity' => -$quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $inventory->quantity,
                'reason' => $reason,
                'user_id' => $user?->id,
            ]);

            return $inventory->fresh();
        });
    }

    public function adjustStock(Product $product, int $newQuantity, ?string $reason = null, ?User $user = null, ?ProductVariant $variant = null): Inventory
    {
        return DB::transaction(function () use ($product, $newQuantity, $reason, $user, $variant) {
            $inventory = $this->getOrCreateInventory($product, $variant);
            $previousQuantity = $inventory->quantity;

            $inventory->update([
                'quantity' => $newQuantity,
                'is_in_stock' => $newQuantity > 0,
            ]);

            $inventory->adjustments()->create([
                'type' => StockAdjustment::TYPE_ADJUSTMENT,
                'quantity' => $newQuantity - $previousQuantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
                'user_id' => $user?->id,
            ]);

            return $inventory->fresh();
        });
    }

    public function recordWastage(Product $product, int $quantity, ?string $reason = null, ?User $user = null, ?ProductVariant $variant = null): Inventory
    {
        return DB::transaction(function () use ($product, $quantity, $reason, $user, $variant) {
            $inventory = $this->getOrCreateInventory($product, $variant);

            if ($inventory->quantity < $quantity) {
                throw new \RuntimeException('মজুদের চেয়ে বেশি পণ্য নষ্ট করা যাবে না।');
            }

            $previousQuantity = $inventory->quantity;

            $inventory->update([
                'quantity' => $inventory->quantity - $quantity,
                'wasted_quantity' => $inventory->wasted_quantity + $quantity,
                'is_in_stock' => $inventory->quantity - $quantity > 0,
            ]);

            $inventory->adjustments()->create([
                'type' => StockAdjustment::TYPE_WASTAGE,
                'quantity' => -$quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $inventory->quantity,
                'reason' => $reason,
                'user_id' => $user?->id,
            ]);

            return $inventory->fresh();
        });
    }

    public function recordDamage(Product $product, int $quantity, ?string $reason = null, ?User $user = null, ?ProductVariant $variant = null): Inventory
    {
        return DB::transaction(function () use ($product, $quantity, $reason, $user, $variant) {
            $inventory = $this->getOrCreateInventory($product, $variant);

            if ($inventory->quantity < $quantity) {
                throw new \RuntimeException('মজুদের চেয়ে বেশি পণ্য ক্ষতিগ্রস্ত করা যাবে না।');
            }

            $previousQuantity = $inventory->quantity;

            $inventory->update([
                'quantity' => $inventory->quantity - $quantity,
                'damaged_quantity' => $inventory->damaged_quantity + $quantity,
                'is_in_stock' => $inventory->quantity - $quantity > 0,
            ]);

            $inventory->adjustments()->create([
                'type' => StockAdjustment::TYPE_DAMAGE,
                'quantity' => -$quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $inventory->quantity,
                'reason' => $reason,
                'user_id' => $user?->id,
            ]);

            return $inventory->fresh();
        });
    }

    public function updateLowStockThreshold(Product $product, int $threshold, ?ProductVariant $variant = null): Inventory
    {
        $inventory = $this->getOrCreateInventory($product, $variant);

        $inventory->update([
            'low_stock_threshold' => $threshold,
        ]);

        return $inventory->fresh();
    }
}
