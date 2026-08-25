<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'weight',
        'unit',
        'price',
        'discount_price',
        'minimum_order',
        'maximum_order',
        'is_active',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'minimum_order' => 'integer',
        'maximum_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }
}
