<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomepageService
{
    /**
     * হোমপেজের সব ডায়নামিক ডেটা — এক জায়গায়, eager-loaded।
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [
            'quickCategories' => $this->quickCategories(),
            'featuredProducts' => $this->featuredProducts(),
            'sections' => $this->sections(),
        ];
    }

    /**
     * কুইক ক্যাটাগরি — ফিচার্ড + সক্রিয়, পণ্যসংখ্যাসহ (একটি অতিরিক্ত কুয়েরি)
     */
    public function quickCategories(): Collection
    {
        return Category::query()
            ->active()
            ->featured()
            ->ordered()
            ->withCount(['products as products_count' => fn ($query) => $query->active()])
            ->take(config('shop.homepage.quick_categories_limit'))
            ->get();
    }

    /**
     * বিশেষ পণ্য — active product + active variant, স্টক-অগ্রাধিকারসহ
     */
    public function featuredProducts(): Collection
    {
        return $this->productQuery()
            ->featured()
            ->orderByRaw("CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END")
            ->latest()
            ->take(config('shop.homepage.featured_limit'))
            ->get();
    }

    /**
     * কনফিগ-ম্যাপড সেকশনসমূহ — ক্যাটাগরি/পণ্য না থাকলে সেকশন বাদ যায় (graceful)
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function sections(): Collection
    {
        return collect(config('shop.homepage.sections'))
            ->map(fn (array $config, string $key) => $this->section($key))
            ->filter()
            ->values();
    }

    /**
     * নির্দিষ্ট সেকশনের ডেটা — slug-config → ক্যাটাগরি(+বংশধর) → active পণ্য
     *
     * @return array{key: string, title: string, subtitle: string, category: Category, products: Collection}|null
     */
    public function section(string $key): ?array
    {
        $config = config("shop.homepage.sections.{$key}");

        if (! $config || empty($config['slugs'])) {
            return null;
        }

        // একটি কুয়েরিতে মূল ক্যাটাগরিগুলো (children eager-loaded)
        $roots = Category::query()
            ->active()
            ->whereIn('slug', $config['slugs'])
            ->with('children')
            ->get();

        if ($roots->isEmpty()) {
            return null;
        }

        $categoryIds = $roots->flatMap(
            fn (Category $root) => array_merge([$root->id], $root->getDescendantIds()),
        )->unique()->values();

        $products = $this->productQuery()
            ->whereHas('category', fn ($query) => $query
                ->whereIn('categories.id', $categoryIds)
                ->where('categories.is_active', true))
            ->orderByDesc('is_featured')
            ->latest()
            ->take($config['limit'])
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'key' => $key,
            'title' => $config['title'],
            'subtitle' => $config['subtitle'] ?? null,
            'category' => $roots->first(),
            'products' => $products,
        ];
    }

    /**
     * কার্ড-রেন্ডারিংয়ের জন্য প্রয়োজনীয় relation সহ পণ্য কুয়েরি
     */
    private function productQuery(): Builder
    {
        return Product::query()
            ->active()
            ->with(['category', 'primaryImage', 'images', 'activeVariants.inventory']);
    }
}
