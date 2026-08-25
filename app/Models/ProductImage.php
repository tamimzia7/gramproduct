<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ছবির সর্বজনীন URL
     */
    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * ডিস্ক থেকে ফাইল মুছে ফেলা
     */
    public function deleteFile(): bool
    {
        $disk = Storage::disk('public');

        if ($disk->exists($this->image_path)) {
            return $disk->delete($this->image_path);
        }

        return false;
    }
}
