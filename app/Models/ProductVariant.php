<?php

namespace App\Models;

use App\Enums\ProductUnit;
use App\Enums\StockStatus;
use App\Support\BengaliNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'unit',
        'quantity',
        'price',
        'compare_at_price',
        'stock_status',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'unit' => ProductUnit::class,
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'stock_status' => StockStatus::class,
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ইনভেন্টরি — প্রতিটি ভ্যারিয়েন্টের নিজস্ব স্টক-স্টেট
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    // ---------- Scope ----------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ---------- Helper ----------

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    // ---------- স্টক স্টেট (ইনভেন্টরি-সচেতন) ----------

    /**
     * উপলব্ধ স্টক — inventory রেকর্ড থাকলে quantity − reserved, নইলে ০
     */
    public function availableQuantity(): int
    {
        return $this->inventory?->available_quantity ?? 0;
    }

    public function isInStock(): bool
    {
        if ($this->stock_status === StockStatus::PRE_ORDER) {
            return true;
        }

        $inventory = $this->inventory;

        return $inventory
            ? ($inventory->available_quantity > 0 || $inventory->allow_backorder)
            : $this->stock_status !== StockStatus::OUT_OF_STOCK;
    }

    public function isLowStock(): bool
    {
        if ($this->stock_status === StockStatus::PRE_ORDER || $this->isOutOfStock()) {
            return false;
        }

        $inventory = $this->inventory;

        return $inventory !== null && $inventory->available_quantity > 0 && $inventory->isLowStock();
    }

    public function isOutOfStock(): bool
    {
        if ($this->stock_status === StockStatus::PRE_ORDER) {
            return false;
        }

        $inventory = $this->inventory;

        return $inventory
            ? $inventory->isOutOfStock()
            : $this->stock_status === StockStatus::OUT_OF_STOCK;
    }

    public function isPurchasable(): bool
    {
        return $this->isActive() && ! $this->isOutOfStock();
    }

    /**
     * কাস্টমার-ফেসিং স্টক লেবেল:
     * "প্রি-অর্ডার" / "স্টক শেষ" / "মাত্র Xটি বাকি" / "স্টকে আছে"
     */
    public function stockLabel(): string
    {
        if ($this->stock_status === StockStatus::PRE_ORDER) {
            return __('product.stock.pre_order');
        }

        if ($this->isOutOfStock()) {
            return __('inventory.statuses.out_of_stock');
        }

        if ($this->isLowStock()) {
            return __('inventory.statuses.low_stock_left', [
                'count' => BengaliNumber::format($this->availableQuantity()),
            ]);
        }

        return __('inventory.statuses.in_stock');
    }

    /**
     * এককের বাংলা লেবেল — "কেজি", "গ্রাম" ইত্যাদি
     */
    public function unitLabel(): string
    {
        return $this->unit?->label() ?? '';
    }

    /**
     * পরিমাণ + এককের বাংলা লেবেল — "৫ কেজি", "৫০০ গ্রাম"
     */
    public function quantityLabel(): string
    {
        $unit = $this->unitLabel();
        $value = (float) $this->quantity;

        // পূর্ণসংখ্যা হলে দশমিক ছাড়া, নইলে শেষের অপ্রয়োজনীয় শূন্য বাদ
        $formatted = $value == (int) $value
            ? number_format($value)
            : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        if ($unit === '') {
            return BengaliNumber::format($formatted);
        }

        return BengaliNumber::format($formatted).' '.$unit;
    }

    /**
     * ক্রস-আউট দেখানোর "আগের মূল্য" — থাকলে এবং বড় হলে
     */
    public function oldPrice(): ?string
    {
        if ($this->compare_at_price !== null && (float) $this->compare_at_price > (float) $this->price) {
            return $this->compare_at_price;
        }

        return null;
    }

    /**
     * ছাড়ের শতকরা হার (পূর্ণসংখ্যা)
     */
    public function discountPercent(): int
    {
        $old = $this->oldPrice();

        if ($old === null) {
            return 0;
        }

        return (int) round(((float) $old - (float) $this->price) / (float) $old * 100);
    }
}
