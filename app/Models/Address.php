<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'division',
        'district',
        'upazila',
        'area',
        'address_line',
        'postal_code',
        'delivery_note',
        'is_default',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ছোট সারাংশ — "তামিম, ০১৭..., গাজীপুর"
     */
    public function summaryLine(): string
    {
        return $this->name.', '.$this->phone.', '.$this->district;
    }
}
