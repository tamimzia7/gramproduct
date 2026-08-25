<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'slug',
        'short_description',
        'description',
        'image',
        'base_price',
        'discount_price',
        'unit',
        'product_type',
        'is_featured',
        'is_bestseller',
        'is_new_arrival',
        'is_active',
        'origin',
        'farmer_name',
        'seasonal_info',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->base_price;
    }

    public function getCategoryBreadcrumb()
    {
        $ancestors = collect();
        $current = $this->category;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse()->values();
    }
}
