<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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
        'is_active',
        'is_featured',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * শুধুমাত্র মূল-স্তরের ক্যাটাগরি (parent_id = null)
     */
    public function scopeRootLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    public function hasParent(): bool
    {
        return $this->parent_id !== null;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the SEO title, falling back to the category name.
     */
    public function getSeoTitleAttribute(): ?string
    {
        return $this->attributes['seo_title'] ?? $this->name;
    }

    /**
     * Get the SEO description, falling back to the category description.
     */
    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->attributes['seo_description'] ?? $this->description;
    }

    /**
     * Get the breadcrumb trail from root to this category.
     *
     * @return Collection<int, Category>
     */
    public function getBreadcrumb(): Collection
    {
        $ancestors = collect();
        $current = $this;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse()->values();
    }

    /**
     * Get all descendant category IDs (children, grandchildren, etc.).
     *
     * @return array<int, int>
     */
    public function getDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Get the full hierarchy path as a formatted string.
     */
    public function getHierarchyPath(): string
    {
        $parts = [];
        $current = $this;

        while ($current) {
            $parts[] = $current->name;
            $current = $current->parent;
        }

        return implode(' → ', array_reverse($parts));
    }
}
