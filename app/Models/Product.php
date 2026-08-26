<?php

namespace App\Models;

use App\Enums\ProductUnit;
use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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
        'compare_at_price',
        'unit',
        'product_type',
        'is_featured',
        'is_bestseller',
        'is_new_arrival',
        'is_seasonal',
        'is_active',
        'stock_status',
        'sort_order',
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
        'compare_at_price' => 'decimal:2',
        'unit' => ProductUnit::class,
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_seasonal' => 'boolean',
        'is_active' => 'boolean',
        'stock_status' => StockStatus::class,
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * কাস্টমার-ফেসিং সক্রিয় ভ্যারিয়েন্ট — প্রদর্শনের ক্রম অনুযায়ী
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->active()->ordered();
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)
            ->where('is_default', true)
            ->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    // ---------- Scope ----------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestseller(Builder $query): Builder
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeNewArrival(Builder $query): Builder
    {
        return $query->where('is_new_arrival', true);
    }

    public function scopeSeasonal(Builder $query): Builder
    {
        return $query->where('is_seasonal', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_status', StockStatus::IN_STOCK->value);
    }

    /**
     * নাম, সংক্ষিপ্ত বিবরণ ও SKU দিয়ে অনুসন্ধান
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ---------- Helper ----------

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * প্রদর্শনযোগ্য ভ্যারিয়েন্ট — ডিফল্ট, না থাকলে প্রথম সক্রিয় ভ্যারিয়েন্ট
     *
     * eager-loaded activeVariants থেকে কাজ করে; আলাদা কুয়েরি চালায় না।
     */
    public function displayVariant(): ?ProductVariant
    {
        if (! $this->relationLoaded('activeVariants')) {
            $this->load('activeVariants');
        }

        return $this->activeVariants->firstWhere('is_default', true)
            ?? $this->activeVariants->first();
    }

    /**
     * পণ্যের অন্তত একটি সক্রিয় ভ্যারিয়েন্ট আছে কি না
     */
    public function hasActiveVariants(): bool
    {
        return $this->displayVariant() !== null;
    }

    public function isInStock(): bool
    {
        return $this->stock_status === StockStatus::IN_STOCK;
    }

    /**
     * কার্টের সাথে সামঞ্জস্যপূর্ণ প্রচলিত মূল্য (বিক্রয়মূল্য)
     */
    public function effectivePrice(): string
    {
        return $this->discount_price ?? $this->base_price;
    }

    /**
     * ক্রস-আউট দেখানোর "আগের মূল্য" — থাকলে এবং বড় হলে
     */
    public function oldPrice(): ?string
    {
        if ($this->compare_at_price !== null && (float) $this->compare_at_price > (float) $this->effectivePrice()) {
            return $this->compare_at_price;
        }

        return null;
    }

    /**
     * ছাড়ের শতকরা হার (পূর্ণসংখ্যা)
     */
    public function discountPercent(): int
    {
        $old = $this->oldPrice();

        if ($old === null) {
            return 0;
        }

        return (int) round(((float) $old - (float) $this->effectivePrice()) / (float) $old * 100);
    }

    /**
     * এককের বাংলা লেবেল
     */
    public function unitLabel(): string
    {
        return $this->unit?->label() ?? '';
    }

    /**
     * প্রধান ছবির URL — product_images → legacy image → null
     */
    public function imageUrl(): ?string
    {
        $primary = $this->primaryImage;

        if ($primary) {
            return $primary->url();
        }

        $first = $this->images->first();

        if ($first) {
            return $first->url();
        }

        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return null;
    }

    /**
     * ছবির alt টেক্সট — বাংলা
     */
    public function imageAltText(): string
    {
        return $this->primaryImage?->alt_text
            ?? $this->images->first()?->alt_text
            ?? $this->name;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price < $this->base_price;
    }

    /**
     * SEO শিরোনাম — fallback: পণ্যের নাম
     */
    public function getSeoTitle(): string
    {
        return $this->seo_title ?? $this->name;
    }

    /**
     * SEO বিবরণ — fallback: সংক্ষিপ্ত বিবরণ
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo_description ?? $this->short_description;
    }

    /**
     * ক্যাটাগরি চেইন থেকে breadcrumb (root → leaf)
     *
     * @return Collection<int, Category>
     */
    public function getCategoryBreadcrumb(): Collection
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
