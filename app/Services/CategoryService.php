<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Generate a unique slug from the given name.
     * If the slug already exists, append a numeric suffix.
     */
    public function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = Category::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $query = Category::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $counter++;
        }

        return $slug;
    }

    /**
     * Validate that a category can be assigned to the given parent.
     * Prevents:
     * - Setting itself as its own parent
     * - Moving a category under one of its own descendants (circular hierarchy)
     */
    public function validateHierarchy(?int $parentId, ?int $categoryId = null): bool
    {
        // No parent is always valid
        if ($parentId === null) {
            return true;
        }

        // Cannot set itself as its own parent
        if ($categoryId !== null && $parentId === $categoryId) {
            return false;
        }

        // Cannot move under a descendant
        if ($categoryId !== null) {
            $descendantIds = $this->getDescendantIds($categoryId);

            if (in_array($parentId, $descendantIds, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all descendant IDs for a given category.
     *
     * @return array<int, int>
     */
    public function getDescendantIds(int $categoryId): array
    {
        $category = Category::findOrFail($categoryId);

        return $category->getDescendantIds();
    }

    /**
     * Handle category image upload. Returns the path to the stored image.
     */
    public function handleImageUpload(mixed $image): ?string
    {
        if (! $image) {
            return null;
        }

        return $image->store('categories', 'public');
    }

    /**
     * Delete the old image when replacing or deleting a category.
     */
    public function deleteImage(?string $imagePath): bool
    {
        if (! $imagePath) {
            return false;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($imagePath)) {
            return $disk->delete($imagePath);
        }

        return false;
    }

    /**
     * Build a flat list of categories for select dropdowns.
     * Includes indentation for hierarchy visualization.
     *
     * @return array<int, array{id: int, name: string, disabled: bool}>
     */
    public function getSelectableCategories(?int $excludeId = null): array
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $excludeDescendantIds = [];
        if ($excludeId) {
            $excludeDescendantIds = $this->getDescendantIds($excludeId);
            $excludeDescendantIds[] = $excludeId;
        }

        $flat = [];

        foreach ($categories as $category) {
            $depth = 0;
            $current = $category;

            while ($current->parent_id) {
                $depth++;
                $current = Category::find($current->parent_id);
                if (! $current) {
                    break;
                }
            }

            $indent = str_repeat('— ', $depth);
            $isExcluded = in_array($category->id, $excludeDescendantIds, true);

            $flat[] = [
                'id' => $category->id,
                'name' => $indent.$category->name,
                'disabled' => $isExcluded,
            ];
        }

        return $flat;
    }
}
