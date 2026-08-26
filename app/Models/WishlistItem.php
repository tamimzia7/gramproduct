<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ইচ্ছেতালিকা পণ্য-ভিত্তিক; কেনার ভ্যারিয়েন্ট কার্টেই ঠিক হয়
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
