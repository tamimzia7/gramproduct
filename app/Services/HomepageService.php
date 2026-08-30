<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
     * হোমপেজ ক্যাটাগরি সেকশন — সব active top-level ক্যাটাগরি, কোনো সংখ্যা-সীমা নেই।
     *
     * - active (is_active = true)
     * - parent_id = null (শুধু মূল-স্তর; child-রা ক্যাটাগরি পেজে browse হয়)
     * - existing sort_order অনুযায়ী
     * - একটি aggregate কুয়েরিতে active-product count (N+1 নেই)
     */
    public function quickCategories(): Collection
    {
        return Category::query()
            ->active()
            ->rootLevel()
            ->ordered()
            ->withCount(['products as products_count' => fn ($query) => $query->active()])
            ->get();
    }

    /**
     * সেরা পণ্য — merchandising strategy:
     * ১) featured (স্টক-অগ্রাধিকার) → ২) ধানক্ষেত (rice) fill → ৩) সর্বশেষ বাকিরা।
     *
     * Rice-priority config('shop.homepage.sections.rice.slugs') থেকেই আসে —
     * শতকরা হার বা ID কোথাও hard-code করা নেই; মোট সংখ্যা limit-এ সীমাবদ্ধ।
     */
    public function featuredProducts(): Collection
    {
        $limit = (int) config('shop.homepage.featured_limit');

        $featured = $this->productQuery()
            ->featured()
            ->orderByRaw("CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END")
            ->latest()
            ->take($limit)
            ->get();

        if ($featured->count() >= $limit) {
            return $featured;
        }

        $excludeIds = $featured->pluck('id')->all();

        // ২) rice representation — config-mapped category tree
        $riceCategoryIds = Category::query()
            ->active()
            ->whereIn('slug', config('shop.homepage.sections.rice.slugs', []))
            ->with('children')
            ->get()
            ->flatMap(fn (Category $root) => array_merge([$root->id], $root->getDescendantIds()))
            ->unique()
            ->values();

        $rice = collect();

        if ($riceCategoryIds->isNotEmpty()) {
            $rice = $this->productQuery()
                ->whereHas('category', fn ($query) => $query
                    ->whereIn('categories.id', $riceCategoryIds)
                    ->where('categories.is_active', true))
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('is_featured')
                ->latest()
                ->take($limit - $featured->count())
                ->get();
        }

        $excludeIds = array_merge($excludeIds, $rice->pluck('id')->all());

        // ৩) এখনও ঘাটতি থাকলে সর্বশেষ active পণ্য দিয়ে পূরণ
        $fill = collect();
        if ($featured->count() + $rice->count() < $limit) {
            $fill = $this->productQuery()
                ->whereNotIn('id', $excludeIds)
                ->latest()
                ->take($limit - $featured->count() - $rice->count())
                ->get();
        }

        return $featured
            ->concat($rice)
            ->concat($fill)
            ->unique('id')
            ->values();
    }

    /**
     * কনফিগ-ম্যাপড সেকশনসমূহ — ক্যাটাগরি/পণ্য না থাকলে সেকশন বাদ যায় (graceful)
     *
     * চাল (rice) ও মাছ (fish) আলাদা ডেডিকেটেড শোকেসে (riceShowcase/fishShowcase)
     * দেখানো হয়; তাই এগুলো এখানে বাদ দেওয়া হয় যাতে একই পণ্য দুইবার না আসে।
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function sections(): Collection
    {
        $excludingDedicated = collect(config('shop.homepage.sections'))
            ->except(['rice', 'fish'])
            ->map(fn (array $config, string $key) => $this->section($key))
            ->filter()
            ->values();

        return $excludingDedicated;
    }

    /**
     * ডেডিকেটেড রাইস শোকেস — চাল মূল ক্যাটাগরি (+বংশধর) থেকে active পণ্য
     */
    public function riceShowcase(): ?array
    {
        return $this->showcaseFromConfig('rice_showcase');
    }

    /**
     * ডেডিকেটেড ফ্রেশ ফিশ শোকেস — মাছ মূল ক্যাটাগরি (+বংশধর) থেকে active পণ্য
     */
    public function fishShowcase(): ?array
    {
        return $this->showcaseFromConfig('fish_showcase');
    }

    /**
     * কনফিগ-ম্যাপড ডেডিকেটেড শোকেস — মূল ক্যাটাগরি (+বংশধর) থেকে active পণ্য,
     * সক্রিয় child ক্যাটাগরিগুলো quick-link হিসেবে। সম্পূর্ণ slug-ভিত্তিক।
     *
     * @return array{rootCategory: Category, children: Collection<int, Category>, products: Collection, productCount: int}|null
     */
    private function showcaseFromConfig(string $configKey): ?array
    {
        $config = config("shop.homepage.{$configKey}");

        if (! $config || empty($config['slugs'])) {
            return null;
        }

        // এক কুয়েরিতে মূল ক্যাটাগরি — child-রা eager-loaded
        $roots = Category::query()
            ->active()
            ->whereIn('slug', $config['slugs'])
            ->with('children')
            ->get();

        if ($roots->isEmpty()) {
            return null;
        }

        $root = $roots->first();

        $categoryIds = array_merge([$root->id], $root->getDescendantIds());

        // quick-link-এর জন্য শুধুমাত্র active child ক্যাটাগরি
        $children = $root->children
            ->filter(fn (Category $child) => $child->is_active)
            ->values();

        $products = $this->productQuery()
            ->whereHas('category', fn ($query) => $query
                ->whereIn('categories.id', $categoryIds)
                ->where('categories.is_active', true))
            ->orderByDesc('is_featured')
            ->latest()
            ->take((int) $config['limit'])
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        $productCount = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query
                ->whereIn('categories.id', $categoryIds)
                ->where('categories.is_active', true))
            ->count();

        return [
            'rootCategory' => $root,
            'children' => $children,
            'products' => $products,
            'productCount' => $productCount,
        ];
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
     * ক্রেতার মতামত — শুধুমাত্র অ্যাপ্রুভড/প্রকাশিত রিভিউ (moderation সাপেক্ষে)।
     *
     * রিভিউ সিস্টেম এখনো নেই (কোনো Review model/table নেই), তাই বর্তমানে খালি
     * collection ফেরত দেয় এবং হোমপেজে সেকশন দেখানো হয় না। ভবিষ্যতে রিভিউ
     * সিস্টেম যোগ হলে এই নির্দিষ্ট কুয়েরি স্বয়ংক্রিয়ভাবে কাজ শুরু করবে।
     * কখনোই কৃত্রিম/ফেক রিভিউ বা নাম তৈরি করা হয় না।
     *
     * @return Collection<int, Model>
     */
    public function testimonials(): Collection
    {
        $reviewClass = 'App\Models\Review';

        if (! class_exists($reviewClass)) {
            return collect();
        }

        return $reviewClass::query()
            ->approved()
            ->with(['product:id,name,slug'])
            ->latest()
            ->take((int) config('shop.homepage.review_limit', 6))
            ->get();
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
