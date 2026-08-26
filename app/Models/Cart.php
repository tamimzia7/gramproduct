<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'currency',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * সাবটোটাল — সব item-এর line_total এর যোগফল
     */
    public function getSubtotalAttribute(): float
    {
        return round($this->items->sum(fn (CartItem $item) => $item->line_total), 2);
    }

    /**
     * মোট পরিমাণ (quantity সমষ্টি)
     */
    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
