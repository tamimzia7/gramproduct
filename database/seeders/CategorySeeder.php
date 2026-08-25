<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // মূল ক্যাটাগরিসমূহ
        $rice = Category::create([
            'name' => 'চাল ও ডাল',
            'slug' => 'rice-grains',
            'description' => 'স্থানীয় খামার থেকে সংগৃহীত টাটকা চাল ও ডাল।',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
            'seo_title' => 'চাল ও ডাল - গ্রামপ্রোডাক্ট',
            'seo_description' => 'সরাসরি কৃষকের কাছ থেকে টাটকা চাল ও ডাল কিনুন।',
        ]);

        $fish = Category::create([
            'name' => 'মাছ',
            'slug' => 'fish-seafood',
            'description' => 'নদী ও পুকুর থেকে সংগৃহীত টাটকা মাছ।',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
            'seo_title' => 'মাছ - গ্রামপ্রোডাক্ট',
            'seo_description' => 'স্থানীয় উৎস থেকে সংগৃহীত টাটকা দেশি মাছ।',
        ]);

        $vegetables = Category::create([
            'name' => 'সবজি',
            'slug' => 'vegetables',
            'description' => 'গ্রামের খামারে জৈব পদ্ধতিতে চাষ করা সবজি।',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 3,
            'seo_title' => 'সবজি - গ্রামপ্রোডাক্ট',
            'seo_description' => 'গ্রামের খামার থেকে টাটকা জৈব সবজি।',
        ]);

        $fruits = Category::create([
            'name' => 'ফল',
            'slug' => 'fruits',
            'description' => 'স্থানীয় বাগান থেকে মৌসুমি ফল।',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 4,
        ]);

        $dairy = Category::create([
            'name' => 'দুধ ও দুগ্ধজাত পণ্য',
            'slug' => 'dairy-products',
            'description' => 'টাটকা দুধ, দই ও অন্যান্য দুগ্ধজাত পণ্য।',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 5,
        ]);

        $spices = Category::create([
            'name' => 'মসলা ও ভেষজ',
            'slug' => 'spices-herbs',
            'description' => 'খাঁটি গ্রামীণ মসলা ও ভেষজ।',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 6,
        ]);

        $snacks = Category::create([
            'name' => 'নাস্তা ও মিষ্টি',
            'slug' => 'snacks-sweets',
            'description' => 'ঐতিহ্যবাহী গ্রামীণ নাস্তা ও মিষ্টান্ন।',
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 7,
        ]);

        // সাব-ক্যাটাগরি: চাল ও ডাল
        Category::create([
            'parent_id' => $rice->id,
            'name' => 'কাটারিভোগ চাল',
            'slug' => 'kataribhog-rice',
            'description' => 'প্রিমিয়াম মানের সুগন্ধি কাটারিভোগ চাল।',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::create([
            'parent_id' => $rice->id,
            'name' => 'লাল চাল',
            'slug' => 'brown-rice',
            'description' => 'স্বাস্থ্যকর লাল চালের বিকল্প।',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // সাব-ক্যাটাগরি: মাছ
        Category::create([
            'parent_id' => $fish->id,
            'name' => 'মিঠে পানির মাছ',
            'slug' => 'freshwater-fish',
            'description' => 'স্থানীয় পুকুর ও নদীর মাছ।',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::create([
            'parent_id' => $fish->id,
            'name' => 'শুঁটকি মাছ',
            'slug' => 'dried-fish',
            'description' => 'ঐতিহ্যবাহী রোদে শুকানো শুঁটকি।',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // সাব-ক্যাটাগরি: সবজি
        Category::create([
            'parent_id' => $vegetables->id,
            'name' => 'শাকসবজি',
            'slug' => 'leafy-greens',
            'description' => 'টাটকা শাক ও সবজি।',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::create([
            'parent_id' => $vegetables->id,
            'name' => 'মূলজাতীয় সবজি',
            'slug' => 'root-vegetables',
            'description' => 'আলু, গাজরসহ মূলজাতীয় সবজি।',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // সাব-ক্যাটাগরি: ফল
        Category::create([
            'parent_id' => $fruits->id,
            'name' => 'আম',
            'slug' => 'mangoes',
            'description' => 'মিষ্টি গ্রামীণ আম।',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // সাব-ক্যাটাগরি: দুগ্ধজাত
        Category::create([
            'parent_id' => $dairy->id,
            'name' => 'কাঁচা দুধ',
            'slug' => 'milk',
            'description' => 'গ্রামের টাটকা গরুর দুধ।',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // সাব-ক্যাটাগরি: মসলা
        Category::create([
            'parent_id' => $spices->id,
            'name' => 'হলুদ',
            'slug' => 'turmeric',
            'description' => 'জৈব পদ্ধতিতে উৎপাদিত গ্রামীণ হলুদ।',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
