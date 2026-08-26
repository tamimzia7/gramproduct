<?php

namespace Database\Seeders;

use App\Enums\ProductUnit;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * বাংলা ডেমো পণ্য — CategorySeeder-এর ক্যাটাগরিগুলোর সাথে সংযুক্ত
     */
    public function run(): void
    {
        $data = [
            // slug => [name, sku, parent-slug, price, compare, unit, flags]
            [
                'slug' => 'nazirshail-chal',
                'category' => 'kataribhog-rice',
                'name' => 'নাজিরশাইল চাল',
                'sku' => 'RICE-NS-001',
                'short_description' => 'গ্রামের মিল থেকে সংগ্রহ করা উন্নত মানের নাজিরশাইল চাল।',
                'description' => "দিনাজপুরের উঁচু জমির ধান থেকে তৈরি খাঁটি নাজিরশাইল চাল।\nসম্পূর্ণ ভেজানো ছাড়াই ঐতিহ্যবাহী পদ্ধতিতে মিলে ভাঙা। প্রতি ব্যাচে উৎস ও কৃষকের তথ্য সংরক্ষিত।",
                'base_price' => 120,
                'compare_at_price' => 140,
                'unit' => ProductUnit::KG,
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new_arrival' => true,
                'origin' => 'দিনাজপুর',
                'farmer_name' => 'আব্দুল করিম',
            ],
            [
                'slug' => 'lal-chal',
                'category' => 'brown-rice',
                'name' => 'লাল চাল',
                'sku' => 'RICE-BR-001',
                'short_description' => 'ফাইবারসমৃদ্ধ দেশি লাল চাল, স্বাস্থ্যসচেতনদের জন্য আদর্শ।',
                'description' => 'রাসায়নিক সার ছাড়া চাষ করা লাল চাল। ফোড়ন ছাড়া ভাত, খিচুড়ি বা পিঠার জন্য উপযুক্ত।',
                'base_price' => 95,
                'compare_at_price' => null,
                'unit' => ProductUnit::KG,
                'is_featured' => false,
                'is_new_arrival' => true,
                'origin' => 'রংপুর',
            ],
            [
                'slug' => 'desi-koi-mach',
                'category' => 'freshwater-fish',
                'name' => 'দেশি কৈ মাছ',
                'sku' => 'FISH-KOI-001',
                'short_description' => 'খাঁটি পুকুরের দেশি কৈ — অতি সুস্বাদু ও পুষ্টিকর।',
                'description' => "প্রাকৃতিক পুকুরে চাষ করা দেশি জাতের কৈ মাছ।\nধরার পর বরফে সজ্জিত করে দ্রুত ডেলিভারি।",
                'base_price' => 380,
                'compare_at_price' => 420,
                'unit' => ProductUnit::KG,
                'is_featured' => true,
                'origin' => 'ঝিনাইদহ',
                'farmer_name' => 'রফিকুল ইসলাম',
            ],
            [
                'slug' => 'shutki-mach',
                'category' => 'dried-fish',
                'name' => 'লোনা শুঁটকি মাছ',
                'sku' => 'FISH-SHT-001',
                'short_description' => 'সুন্দরবন অঞ্চলের ঐতিহ্যবাহী শুঁটকি।',
                'description' => 'রোদে শুকানো খাঁটি লোনা শুঁটকি। ঝাল ভর্তা বা ডাল-ভাজার সাথে অতুলনীয়।',
                'base_price' => 850,
                'compare_at_price' => null,
                'unit' => ProductUnit::KG,
                'is_seasonal' => true,
                'origin' => 'খুলনা',
            ],
            [
                'slug' => 'taza-lau',
                'category' => 'root-vegetables',
                'name' => 'তাজা লাউ',
                'sku' => 'VEG-LAU-001',
                'short_description' => 'গ্রামের খামার থেকে টাটকা বাছাই করা লাউ।',
                'description' => 'প্রতিদিন সকালে মাঠ থেকে সংগ্রহ করা টাটকা লাউ। ঘরে ঘরে ডেলিভারির আগে কোনো প্রিজারভেটিভ ব্যবহার হয় না।',
                'base_price' => 45,
                'compare_at_price' => null,
                'unit' => ProductUnit::PIECE,
                'is_new_arrival' => true,
                'is_seasonal' => true,
                'origin' => 'যশোর',
            ],
            [
                'slug' => 'desi-kacha-morich',
                'category' => 'leafy-greens',
                'name' => 'দেশি কাঁচা মরিচ',
                'sku' => 'VEG-MRC-001',
                'short_description' => 'ঝালে ভরপুর দেশি জাতের কাঁচা মরিচ।',
                'description' => 'সেচ-নির্ভর জমিতে চাষ করা দেশি কাঁচা মরিচ। প্রতিদিনের বাজারের চাহিদা মেটাতে টাটকা সরবরাহ।',
                'base_price' => 60,
                'compare_at_price' => 75,
                'unit' => ProductUnit::KG,
                'is_featured' => true,
                'origin' => 'বগুড়া',
            ],
            [
                'slug' => 'himshagar-aam',
                'category' => 'mangoes',
                'name' => 'হিমসাগর আম',
                'sku' => 'FRUIT-AAM-001',
                'short_description' => 'কার্বাইডমুক্ত পাকা হিমসাগর আম।',
                'description' => "চাঁপাইনবাবগঞ্জের বাগান থেকে সংগৃহীত কার্বাইডমুক্ত হিমসাগর আম।\nমৌসুমি — সরবরাহ সীমিত।",
                'base_price' => 110,
                'compare_at_price' => 130,
                'unit' => ProductUnit::KG,
                'is_featured' => true,
                'is_seasonal' => true,
                'is_new_arrival' => true,
                'origin' => 'চাঁপাইনবাবগঞ্জ',
                'farmer_name' => 'শাহজাহান আলী',
            ],
            [
                'slug' => 'kacha-dudh',
                'category' => 'milk',
                'name' => 'কাঁচা গরুর দুধ',
                'sku' => 'DAIRY-DUDH-001',
                'short_description' => 'সকালের টাটকা খাঁটি গরুর দুধ।',
                'description' => 'স্থানীয় খামারের স্বাস্থ্য পরীক্ষিত গাভীর দুধ। ফরমালিন ও পানি মেশানো হয় না।',
                'base_price' => 80,
                'compare_at_price' => null,
                'unit' => ProductUnit::LITER,
                'is_bestseller' => true,
                'origin' => 'সিরাজগঞ্জ',
            ],
            [
                'slug' => 'desi-holud-gura',
                'category' => 'turmeric',
                'name' => 'দেশি হলুদ গুঁড়া',
                'sku' => 'SPICE-HLD-001',
                'short_description' => 'ঘরে বসানো পদ্ধতিতে ভাঙা খাঁটি হলুদ গুঁড়া।',
                'description' => 'রোদে শুকানো দেশি হলুদ থেকে বিশেষ যত্নে ভাঙা। কোনো কৃত্রিম রঙ বা ভেজাল নেই।',
                'base_price' => 240,
                'compare_at_price' => 280,
                'unit' => ProductUnit::PACK,
                'is_featured' => false,
                'is_bestseller' => true,
                'origin' => 'কুষ্টিয়া',
            ],
            [
                'slug' => 'khejur-gur',
                'category' => 'snacks-sweets',
                'name' => 'খেজুর গুড়',
                'sku' => 'SWEET-GUR-001',
                'short_description' => 'শীতের ঐতিহ্যবাহী খেজুর রসের গুড়।',
                'description' => "যশোরের খেজুর গাছ থেকে সংগ্রহ করা রস থেকে তৈরি খেজুর গুড়।\nশীত মৌসুমের বিশেষ সংগ্রহ।",
                'base_price' => 450,
                'compare_at_price' => 500,
                'unit' => ProductUnit::BOTTLE,
                'is_seasonal' => true,
                'is_featured' => true,
                'origin' => 'যশোর',
            ],
        ];

        foreach ($data as $item) {
            $category = Category::where('slug', $item['category'])->first();

            if (! $category || Product::where('sku', $item['sku'])->exists()) {
                continue;
            }

            $product = new Product([
                'category_id' => $category->id,
                'name' => $item['name'],
                'sku' => $item['sku'],
                'slug' => $item['slug'],
                'short_description' => $item['short_description'],
                'description' => $item['description'] ?? null,
                'base_price' => $item['base_price'],
                'compare_at_price' => $item['compare_at_price'],
                'unit' => $item['unit'],
                'product_type' => 'physical',
                'is_active' => true,
                'stock_status' => 'in_stock',
                'sort_order' => 0,
                'origin' => $item['origin'] ?? null,
                'farmer_name' => $item['farmer_name'] ?? null,
                'seasonal_info' => ($item['is_seasonal'] ?? false) ? 'নির্দিষ্ট মৌসুমে সরবরাহ হয়।' : null,
            ]);

            $product->is_featured = $item['is_featured'] ?? false;
            $product->is_bestseller = $item['is_bestseller'] ?? false;
            $product->is_new_arrival = $item['is_new_arrival'] ?? false;
            $product->is_seasonal = $item['is_seasonal'] ?? false;

            $product->save();
        }

        // একটি স্টক-শেষ ডেমো পণ্য (empty-state/ব্যাজ যাচাইয়ের জন্য)
        $outCategory = Category::where('slug', 'fish-seafood')->first();

        if ($outCategory && ! Product::where('sku', 'FISH-HIL-001')->exists()) {
            $product = new Product([
                'category_id' => $outCategory->id,
                'name' => 'ইলিশ মাছ (পদ্মা)',
                'sku' => 'FISH-HIL-001',
                'slug' => 'padma-ilish',
                'short_description' => 'পদ্মার খাঁটি ইলিশ — এখন স্টকে নেই।',
                'base_price' => 1600,
                'compare_at_price' => 1800,
                'unit' => ProductUnit::KG,
                'product_type' => 'physical',
                'is_active' => true,
                'stock_status' => 'out_of_stock',
                'origin' => 'পদ্মা অঞ্চল',
            ]);
            $product->save();
        }

        $this->seedVariants();
    }

    /**
     * Phase 04 — ভ্যারিয়েন্ট ডেমো ডেটা (নাজিরশাইল চাল, দেশি কৈ মাছ, খাঁটি সরিষার তেল)
     */
    private function seedVariants(): void
    {
        // খাঁটি সরিষার তেল — ML/LITER ভ্যারিয়েন্টসহ নতুন ডেমো পণ্য
        if (! Product::where('sku', 'OIL-MUSTARD-001')->exists()) {
            $oilCategory = Category::where('slug', 'spices-herbs')->first();

            if ($oilCategory) {
                Product::create([
                    'category_id' => $oilCategory->id,
                    'name' => 'খাঁটি সরিষার তেল',
                    'sku' => 'OIL-MUSTARD-001',
                    'slug' => 'khati-sorishar-tel',
                    'short_description' => 'ঘানিভাঙা খাঁটি সরিষার তেল — সম্পূর্ণ অখাদ্য রাসায়নিকমুক্ত।',
                    'description' => "দেশি সরিষা থেকে ঐতিহ্যবাহী ঘানিতে ভাঙা খাঁটি সরিষার তেল।\nরান্না, আচার ও চুল-ত্বকের যত্নে ব্যবহারযোগ্য।",
                    'base_price' => 320,
                    'compare_at_price' => null,
                    'unit' => ProductUnit::LITER,
                    'product_type' => 'physical',
                    'is_active' => true,
                    'stock_status' => 'in_stock',
                    'is_featured' => true,
                    'sort_order' => 0,
                    'origin' => 'টাঙ্গাইল',
                ]);
            }
        }

        // slug => ভ্যারিয়েন্ট তালিকা
        $variantsBySlug = [
            'nazirshail-chal' => [
                ['name' => '১ কেজি', 'sku' => 'RICE-NS-1KG', 'unit' => 'kg', 'quantity' => 1, 'price' => 120, 'compare_at_price' => 140, 'stock_status' => 'in_stock', 'is_default' => true, 'sort_order' => 1],
                ['name' => '৫ কেজি', 'sku' => 'RICE-NS-5KG', 'unit' => 'kg', 'quantity' => 5, 'price' => 570, 'compare_at_price' => null, 'stock_status' => 'in_stock', 'is_default' => false, 'sort_order' => 2],
                ['name' => '১০ কেজি', 'sku' => 'RICE-NS-10KG', 'unit' => 'kg', 'quantity' => 10, 'price' => 1100, 'compare_at_price' => 1200, 'stock_status' => 'in_stock', 'is_default' => false, 'sort_order' => 3],
                ['name' => '২৫ কেজি', 'sku' => 'RICE-NS-25KG', 'unit' => 'bag', 'quantity' => 25, 'price' => 2600, 'compare_at_price' => null, 'stock_status' => 'pre_order', 'is_default' => false, 'sort_order' => 4],
            ],
            'desi-koi-mach' => [
                ['name' => '৫০০ গ্রাম', 'sku' => 'FISH-KOI-500G', 'unit' => 'gram', 'quantity' => 500, 'price' => 200, 'compare_at_price' => null, 'stock_status' => 'in_stock', 'is_default' => false, 'sort_order' => 1],
                ['name' => '১ কেজি', 'sku' => 'FISH-KOI-1KG', 'unit' => 'kg', 'quantity' => 1, 'price' => 380, 'compare_at_price' => 420, 'stock_status' => 'in_stock', 'is_default' => true, 'sort_order' => 2],
                ['name' => '২ কেজি', 'sku' => 'FISH-KOI-2KG', 'unit' => 'kg', 'quantity' => 2, 'price' => 740, 'compare_at_price' => null, 'stock_status' => 'in_stock', 'is_default' => false, 'sort_order' => 3],
            ],
            'khati-sorishar-tel' => [
                ['name' => '২৫০ মিলিলিটার', 'sku' => 'OIL-MST-250ML', 'unit' => 'ml', 'quantity' => 250, 'price' => 95, 'compare_at_price' => null, 'stock_status' => 'in_stock', 'is_default' => false, 'sort_order' => 1],
                ['name' => '৫০০ মিলিলিটার', 'sku' => 'OIL-MST-500ML', 'unit' => 'ml', 'quantity' => 500, 'price' => 180, 'compare_at_price' => null, 'stock_status' => 'in_stock', 'is_default' => false, 'sort_order' => 2],
                ['name' => '১ লিটার', 'sku' => 'OIL-MST-1LTR', 'unit' => 'liter', 'quantity' => 1, 'price' => 340, 'compare_at_price' => 380, 'stock_status' => 'in_stock', 'is_default' => true, 'sort_order' => 3],
            ],
        ];

        foreach ($variantsBySlug as $slug => $variants) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            foreach ($variants as $variantData) {
                $product->variants()->firstOrCreate(
                    ['sku' => $variantData['sku']],
                    $variantData,
                );
            }
        }
    }
}
