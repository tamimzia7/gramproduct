<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_COD = 'cod';

    protected $fillable = [
        'user_id',
        'order_number',
        'address_id',
        'receiver_name',
        'receiver_phone',
        'division',
        'district',
        'upazila',
        'area',
        'address_line',
        'postal_code',
        'delivery_note',
        'delivery_method',
        'subtotal',
        'delivery_fee',
        'grand_total',
        'currency',
        'payment_method',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'address_id' => 'integer',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
