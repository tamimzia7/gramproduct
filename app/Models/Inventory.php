<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'quantity',
        'reserved_quantity',
        'damaged_quantity',
        'wasted_quantity',
        'low_stock_threshold',
        'is_in_stock',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'product_variant_id' => 'integer',
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'damaged_quantity' => 'integer',
        'wasted_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_in_stock' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }
}
