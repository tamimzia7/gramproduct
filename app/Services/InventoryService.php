<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Exceptions\InventoryException;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * ভ্যারিয়েন্টের ইনভেন্টরি রেকর্ড নিশ্চিত করা (না থাকলে তৈরি)
     */
    public function ensureInventory(ProductVariant $variant, array $attributes = []): Inventory
    {
        return DB::transaction(function () use ($variant, $attributes): Inventory {
            $inventory = Inventory::where('product_variant_id', $variant->id)->lockForUpdate()->first();

            if ($inventory) {
                return $inventory;
            }

            return Inventory::create(array_merge([
                'product_variant_id' => $variant->id,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'low_stock_threshold' => 10,
                'allow_backorder' => false,
            ], $attributes));
        });
    }

    /**
     * স্টক যোগ (restock/purchase) — type=restock ডিফল্ট
     */
    public function addStock(
        ProductVariant $variant,
        int $quantity,
        ?string $note = null,
        ?User $user = null,
        InventoryTransactionType $type = InventoryTransactionType::RESTOCK,
    ): Inventory {
        $this->assertPositive($quantity);

        return $this->mutate($variant, $user, function (Inventory $inventory) use ($quantity): void {
            $inventory->quantity += $quantity;
        }, $type, $quantity, $note);
    }

    /**
     * স্টক কমানো (damage/expired/sale) — উপলব্ধ স্টকের মধ্যেই সীমাবদ্ধ
     */
    public function removeStock(
        ProductVariant $variant,
        int $quantity,
        InventoryTransactionType $type = InventoryTransactionType::DAMAGE,
        ?string $note = null,
        ?User $user = null,
    ): Inventory {
        $this->assertPositive($quantity);
        $this->assertRemovalType($type);

        return $this->mutate($variant, $user, function (Inventory $inventory) use ($quantity): void {
            if ($inventory->available_quantity < $quantity) {
                throw new InventoryException(__('inventory.errors.insufficient_stock', [
                    'available' => $inventory->available_quantity,
                ]));
            }

            $inventory->quantity -= $quantity;
        }, $type, -$quantity, $note);
    }

    /**
     * ম্যানুয়াল সমন্বয় — স্বাক্ষরিত ডেল্টা (+/-), টাইপ=adjustment
     */
    public function adjustStock(ProductVariant $variant, int $delta, ?string $note = null, ?User $user = null): Inventory
    {
        if ($delta === 0) {
            throw new InventoryException(__('inventory.errors.zero_adjustment'));
        }

        return $this->mutate($variant, $user, function (Inventory $inventory) use ($delta): void {
            $newQuantity = $inventory->quantity + $delta;

            if ($newQuantity < 0) {
                throw new InventoryException(__('inventory.errors.negative_stock'));
            }

            if ($newQuantity < $inventory->reserved_quantity) {
                throw new InventoryException(__('inventory.errors.below_reserved'));
            }

            $inventory->quantity = $newQuantity;
        }, InventoryTransactionType::ADJUSTMENT, $delta, $note);
    }

    /**
     * স্টক সংরক্ষণ (ভবিষ্যৎ checkout/order-এর জন্য) — শুধু উপলব্ধ স্টক থেকেই
     *
     * @param  array{type: string, id: int}|null  $reference
     */
    public function reserve(
        ProductVariant $variant,
        int $quantity,
        ?array $reference = null,
        ?string $note = null,
        ?User $user = null,
    ): Inventory {
        $this->assertPositive($quantity);

        return $this->mutate($variant, $user, function (Inventory $inventory) use ($quantity): void {
            if ($inventory->allow_backorder) {
                // backorder অনুমোদিত হলেও reserved কখনো quantity-কে ছাড়িয়ে যেতে পারবে না
                if ($inventory->reserved_quantity + $quantity > $inventory->quantity) {
                    throw new InventoryException(__('inventory.errors.reserved_exceeds_stock'));
                }
            } elseif ($inventory->available_quantity < $quantity) {
                throw new InventoryException(__('inventory.errors.insufficient_stock', [
                    'available' => $inventory->available_quantity,
                ]));
            }

            $inventory->reserved_quantity += $quantity;
        }, InventoryTransactionType::RESERVATION, $quantity, $note, $reference);
    }

    /**
     * সংরক্ষণ মুক্ত/বাতিল
     *
     * @param  array{type: string, id: int}|null  $reference
     */
    public function releaseReservation(
        ProductVariant $variant,
        int $quantity,
        ?array $reference = null,
        ?string $note = null,
        ?User $user = null,
    ): Inventory {
        $this->assertPositive($quantity);

        return $this->mutate($variant, $user, function (Inventory $inventory) use ($quantity): void {
            if ($inventory->reserved_quantity < $quantity) {
                throw new InventoryException(__('inventory.errors.release_exceeds_reserved'));
            }

            $inventory->reserved_quantity -= $quantity;
        }, InventoryTransactionType::RESERVATION_RELEASE, $quantity, $note, $reference);
    }

    /**
     * ফেরত হ্যান্ডলিং — স্পষ্ট সিদ্ধান্ত প্রয়োজন:
     * - restockable=true → বিক্রয়যোগ্য স্টকে ফেরত যোগ হয় (type=return)
     * - restockable=false → ক্ষতিগ্রস্ত হিসেবে লেজারে ঢোকে না; স্টক অপরিবর্তিত থাকে
     *
     * @param  array{type: string, id: int}|null  $reference
     * @return array{inventory: Inventory, restocked: bool}
     */
    public function handleReturn(
        ProductVariant $variant,
        int $quantity,
        bool $restockable = true,
        ?string $note = null,
        ?User $user = null,
        ?array $reference = null,
    ): array {
        if (! $restockable) {
            return [
                'inventory' => $this->ensureInventory($variant),
                'restocked' => false,
            ];
        }

        return [
            'inventory' => $this->addStock(
                $variant,
                $quantity,
                $note ?? __('inventory.notes.return_restock'),
                $user,
                InventoryTransactionType::RETURN_STOCK,
            ),
            'restocked' => true,
        ];
    }

    /**
     * উপলব্ধ স্টক পরিমাণ
     */
    public function availableQuantity(ProductVariant $variant): int
    {
        $inventory = Inventory::where('product_variant_id', $variant->id)->first();

        return $inventory?->available_quantity ?? 0;
    }

    /**
     * নির্দিষ্ট পরিমাণ কিনতে পারবে কি না (backorder সহ)
     */
    public function canPurchase(ProductVariant $variant, int $quantity = 1): bool
    {
        $inventory = Inventory::where('product_variant_id', $variant->id)->first();

        if (! $inventory) {
            return false;
        }

        if ($inventory->allow_backorder) {
            return true;
        }

        return $inventory->available_quantity >= max(1, $quantity);
    }

    /**
     * সমস্ত স্টক-পরিবর্তনের কেন্দ্রীয় পয়েন্ট:
     * transaction + lockForUpdate → inventory ও inventory_transactions একসাথে সফল/বাতিল
     *
     * @param  callable(Inventory): void  $mutation
     * @param  array{type: string, id: int}|null  $reference
     */
    private function mutate(
        ProductVariant $variant,
        ?User $user,
        callable $mutation,
        InventoryTransactionType $type,
        int $ledgerQuantity,
        ?string $note,
        ?array $reference = null,
    ): Inventory {
        return DB::transaction(function () use ($variant, $user, $mutation, $type, $ledgerQuantity, $note, $reference): Inventory {
            $inventory = Inventory::where('product_variant_id', $variant->id)->lockForUpdate()->first();

            if (! $inventory) {
                $inventory = new Inventory([
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 10,
                    'allow_backorder' => false,
                ]);
                $inventory->product_variant_id = $variant->id;
            }

            $before = $inventory->quantity;

            // লেনদেনের ভেতরে, পরিবর্তনের ঠিক আগে সর্বশেষ স্টক পড়া ও যাচাই
            $mutation($inventory);

            $this->assertConsistent($inventory);

            $after = $inventory->quantity;
            $changed = $after !== $before || $inventory->isDirty();

            if (! $changed) {
                return $inventory;
            }

            $inventory->save();

            InventoryTransaction::create([
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $ledgerQuantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'reference_type' => $reference['type'] ?? null,
                'reference_id' => $reference['id'] ?? null,
                'note' => $note,
                'created_by' => $user?->id,
            ]);

            return $inventory;
        });
    }

    /**
     * ইনভেন্টরি স্টেট ইনভ্যারিয়েন্ট — কখনোই ভঙ্গ হতে পারবে না
     */
    private function assertConsistent(Inventory $inventory): void
    {
        if ($inventory->quantity < 0) {
            throw new InventoryException(__('inventory.errors.negative_stock'));
        }

        if ($inventory->reserved_quantity < 0) {
            throw new InventoryException(__('inventory.errors.negative_reserved'));
        }

        if ($inventory->reserved_quantity > $inventory->quantity) {
            throw new InventoryException(__('inventory.errors.reserved_exceeds_stock'));
        }
    }

    private function assertPositive(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InventoryException(__('inventory.errors.quantity_positive'));
        }
    }

    /**
     * removeStock শুধুমাত্র স্টক-হ্রাসকারী টাইপ গ্রহণ করে
     */
    private function assertRemovalType(InventoryTransactionType $type): void
    {
        if ($type->increasesStock() || $type === InventoryTransactionType::ADJUSTMENT) {
            throw new \InvalidArgumentException("Unsupported removal type [{$type->value}].");
        }
    }
}
