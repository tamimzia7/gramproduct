<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // মূল ক্যাটাগরিসমূহ (slug অনুযায়ী idempotent)
        $roots = [
            'rice-grains' => [
                'name' => 'চাল ও ডাল',
                'description' => 'স্থানীয় খামার থেকে সংগৃহীত টাটকা চাল ও ডাল।',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'seo_title' => 'চাল ও ডাল - গ্রামপ্রোডাক্ট',
                'seo_description' => 'সরাসরি কৃষকের কাছ থেকে টাটকা চাল ও ডাল কিনুন।',
            ],
            'fish-seafood' => [
                'name' => 'মাছ',
                'description' => 'নদী ও পুকুর থেকে সংগৃহীত টাটকা মাছ।',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'seo_title' => 'মাছ - গ্রামপ্রোডাক্ট',
                'seo_description' => 'স্থানীয় উৎস থেকে সংগৃহীত টাটকা দেশি মাছ।',
            ],
            'vegetables' => [
                'name' => 'সবজি',
                'description' => 'গ্রামের খামারে জৈব পদ্ধতিতে চাষ করা সবজি।',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'seo_title' => 'সবজি - গ্রামপ্রোডাক্ট',
                'seo_description' => 'গ্রামের খামার থেকে টাটকা জৈব সবজি।',
            ],
            'fruits' => [
                'name' => 'ফল',
                'description' => 'স্থানীয় বাগান থেকে মৌসুমি ফল।',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            'dairy-products' => [
                'name' => 'দুধ ও দুগ্ধজাত পণ্য',
                'description' => 'টাটকা দুধ, দই ও অন্যান্য দুগ্ধজাত পণ্য।',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            'spices-herbs' => [
                'name' => 'মসলা ও ভেষজ',
                'description' => 'খাঁটি গ্রামীণ মসলা ও ভেষজ।',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            'snacks-sweets' => [
                'name' => 'নাস্তা ও মিষ্টি',
                'description' => 'ঐতিহ্যবাহী গ্রামীণ নাস্তা ও মিষ্টান্ন।',
                'is_active' => false,
                'is_featured' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($roots as $slug => $data) {
            Category::updateOrCreate(['slug' => $slug], $data + ['parent_id' => null]);
        }

        // সাব-ক্যাটাগরিসমূহ [slug => [parent-slug, name, description, sort_order]]
        $children = [
            'kataribhog-rice' => ['rice-grains', 'কাটারিভোগ চাল', 'প্রিমিয়াম মানের সুগন্ধি কাটারিভোগ চাল।', 1],
            'brown-rice' => ['rice-grains', 'লাল চাল', 'স্বাস্থ্যকর লাল চালের বিকল্প।', 2],
            'freshwater-fish' => ['fish-seafood', 'মিঠে পানির মাছ', 'স্থানীয় পুকুর ও নদীর মাছ।', 1],
            'dried-fish' => ['fish-seafood', 'শুঁটকি মাছ', 'ঐতিহ্যবাহী রোদে শুকানো শুঁটকি।', 2],
            'leafy-greens' => ['vegetables', 'শাকসবজি', 'টাটকা শাক ও সবজি।', 1],
            'root-vegetables' => ['vegetables', 'মূলজাতীয় সবজি', 'আলু, গাজরসহ মূলজাতীয় সবজি।', 2],
            'mangoes' => ['fruits', 'আম', 'মিষ্টি গ্রামীণ আম।', 1],
            'milk' => ['dairy-products', 'কাঁচা দুধ', 'গ্রামের টাটকা গরুর দুধ।', 1],
            'turmeric' => ['spices-herbs', 'হলুদ', 'জৈব পদ্ধতিতে উৎপাদিত গ্রামীণ হলুদ।', 1],
        ];

        foreach ($children as $slug => [$parentSlug, $name, $description, $sortOrder]) {
            Category::updateOrCreate(['slug' => $slug], [
                'parent_id' => Category::where('slug', $parentSlug)->value('id'),
                'name' => $name,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
