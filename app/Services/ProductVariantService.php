<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    /**
     * নতুন ভ্যারিয়েন্ট তৈরি — ডিফল্ট-স্টেট ইনভ্যারিয়েন্ট বজায় রেখে।
     *
     * ইনভ্যারিয়েন্ট: পণ্যের অন্তত একটি সক্রিয় ভ্যারিয়েন্ট থাকলে
     * ঠিক একটি সক্রিয় ডিফল্ট ভ্যারিয়েন্ট থাকবে।
     */
    public function create(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data): ProductVariant {
            $variant = new ProductVariant($data);
            $variant->product_id = $product->id;
            $variant->save();

            // প্রতিটি নতুন ভ্যারিয়েন্টের জন্য ইনভেন্টরি রেকর্ড (স্টক ০ দিয়ে শুরু)
            $this->inventoryService->ensureInventory($variant);

            if ((bool) ($data['is_default'] ?? false) && $variant->isActive()) {
                // আগের ডিফল্ট বাদ দিয়ে এই ভ্যারিয়েন্ট ডিফল্ট
                $this->setDefault($variant);

                return $variant->refresh();
            }

            if (! $variant->isActive()) {
                return $variant->refresh();
            }

            // পণ্যের কোনো ডিফল্ট না থাকলে (প্রথম সক্রিয় ভ্যারিয়েন্ট) এটিই ডিফল্ট হবে
            $hasDefault = $product->variants()
                ->active()
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                $variant->forceFill(['is_default' => true])->save();
            }

            return $variant->refresh();
        });
    }

    /**
     * ভ্যারিয়েন্ট আপডেট — ডিফল্ট/সক্রিয় অবস্থা পরিবর্তনেও ইনভ্যারিয়েন্ট ধরে রাখে
     */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data): ProductVariant {
            $wasDefault = $variant->isDefault();
            $product = $variant->product;

            $variant->fill($data)->save();

            if (! $variant->isActive()) {
                // নিষ্ক্রিয় ভ্যারিয়েন্ট কখনো ডিফল্ট থাকতে পারে না
                if ($variant->isDefault()) {
                    $this->promoteFallbackDefault($product);

                    $variant->forceFill(['is_default' => false])->save();
                }

                return $variant->refresh();
            }

            if ((bool) ($data['is_default'] ?? false)) {
                $this->setDefault($variant);
            } elseif ($wasDefault) {
                // ডিফল্ট ভ্যারিয়েন্ট থেকে ডিফল্ট-ফ্ল্যাগ সরালে অন্য সক্রিয় ভ্যারিয়েন্ট ডিফল্ট হয়
                $this->promoteFallbackDefault($product);
            }

            return $variant->refresh();
        });
    }

    /**
     * ভ্যারিয়েন্ট soft-delete — মুছে ফেলার পরেও পণ্যের ডিফল্ট-স্টেট বৈধ থাকে।
     *
     * নীতি: ডিফল্ট ভ্যারিয়েন্ট মুছলে বাকি সক্রিয় ভ্যারিয়েন্টের মধ্য থেকে
     * (sort_order → id অনুযায়ী) প্রথমটি স্বয়ংক্রিয়ভাবে ডিফল্ট হয়।
     * ফলে পণ্য কখনো অবৈধ ডিফল্ট-অবস্থায় থাকে না।
     */
    public function delete(ProductVariant $variant): bool
    {
        return DB::transaction(function () use ($variant): bool {
            $product = $variant->product;
            $wasDefault = $variant->isDefault();

            $deleted = $variant->delete();

            if ($deleted && $wasDefault) {
                $this->promoteFallbackDefault($product);
            }

            return (bool) $deleted;
        });
    }

    /**
     * নির্দিষ্ট ভ্যারিয়েন্টকে ডিফল্ট করা — আগের ডিফল্ট নিঃশর্ত বাদ
     */
    public function setDefault(ProductVariant $variant): void
    {
        DB::transaction(function () use ($variant): void {
            $variant->product->variants()
                ->whereKeyNot($variant->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $variant->forceFill(['is_default' => true])->save();
        });
    }

    /**
     * সক্রিয় / নিষ্ক্রিয় করা — ডিফল্ট নিষ্ক্রিয় হলে fallback ডিফল্ট বসে
     */
    public function setActive(ProductVariant $variant, bool $active): ProductVariant
    {
        return DB::transaction(function () use ($variant, $active): ProductVariant {
            $wasDefault = $variant->isDefault();

            $variant->forceFill(['is_active' => $active])->save();

            if ($wasDefault && ! $active) {
                // নিষ্ক্রিয় ভ্যারিয়েন্ট কখনো ডিফল্ট থাকতে পারে না
                $this->promoteFallbackDefault($variant->product);

                $variant->forceFill(['is_default' => false])->save();
            }

            return $variant->refresh();
        });
    }

    /**
     * fallback ডিফল্ট নির্ধারণ — sort_order → id অনুযায়ী প্রথম সক্রিয় ভ্যারিয়েন্ট
     */
    private function promoteFallbackDefault(Product $product): void
    {
        $candidate = $product->variants()
            ->active()
            ->where('is_default', false)
            ->ordered()
            ->first();

        if ($candidate) {
            $candidate->forceFill(['is_default' => true])->save();
        }
    }
}
