<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'is_active',
        'is_featured',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Return all descendant category ids (used for hierarchy safety checks).
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        $load = function (Collection $nodes) use (&$ids, &$load): void {
            foreach ($nodes as $node) {
                $ids[] = $node->id;
                $load($node->children);
            }
        };

        $load($this->children()->with('children')->get());

        return $ids;
    }

    /**
     * Flatten the full category tree with a computed depth for admin UI.
     */
    public static function getFlatTree(): Collection
    {
        $rows = collect();

        $load = function (Collection $nodes, int $depth) use (&$rows, &$load): void {
            foreach ($nodes as $node) {
                $node->depth = $depth;
                $rows->push($node);
                $load($node->children, $depth + 1);
            }
        };

        $load(self::whereNull('parent_id')->with('children')->ordered()->get(), 0);

        return $rows;
    }
}
