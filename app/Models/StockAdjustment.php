<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    public const TYPE_STOCK_IN = 'stock_in';

    public const TYPE_STOCK_OUT = 'stock_out';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_WASTAGE = 'wastage';

    public const TYPE_DAMAGE = 'damage';

    public const TYPES = [
        self::TYPE_STOCK_IN,
        self::TYPE_STOCK_OUT,
        self::TYPE_ADJUSTMENT,
        self::TYPE_WASTAGE,
        self::TYPE_DAMAGE,
    ];

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'inventory_id' => 'integer',
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
        'user_id' => 'integer',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
