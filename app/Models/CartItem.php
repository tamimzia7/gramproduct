<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'cart_id' => 'integer',
        'product_variant_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * কেনার একক — ভ্যারিয়েন্ট; পণ্য variant->product দিয়েই পাওয়া যায়
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getLineTotalAttribute(): float
    {
        return round($this->quantity * (float) $this->unit_price, 2);
    }
}
