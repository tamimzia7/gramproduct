<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ইনভেন্টরি মডিউল — পুনঃব্যবহারযোগ্য বাংলা স্ট্রিং
    |--------------------------------------------------------------------------
    */

    'title' => 'ইনভেন্টরি',
    'dashboard' => 'ইনভেন্টরি ড্যাশবোর্ড',

    'stats' => [
        'total_variants' => 'মোট পণ্য',
        'in_stock' => 'স্টকে আছে',
        'low_stock' => 'স্টক কম',
        'out_of_stock' => 'স্টক শেষ',
        'total_quantity' => 'মোট স্টক',
        'total_reserved' => 'সংরক্ষিত স্টক',
    ],

    'statuses' => [
        'in_stock' => 'স্টকে আছে',
        'low_stock' => 'স্টক কম',
        'low_stock_left' => 'মাত্র :countটি বাকি',
        'out_of_stock' => 'স্টক শেষ',
        'backorder' => 'ব্যাকঅর্ডার চালু',
        'remaining' => ':countটি বাকি',
    ],

    'types' => [
        'purchase' => 'ক্রয়',
        'sale' => 'বিক্রয়',
        'return' => 'ফেরত',
        'adjustment' => 'সমন্বয়',
        'damage' => 'ক্ষতিগ্রস্ত',
        'expired' => 'মেয়াদোত্তীর্ণ',
        'restock' => 'স্টক যোগ',
        'reservation' => 'সংরক্ষণ',
        'reservation_release' => 'সংরক্ষণ বাতিল',
    ],

    'fields' => [
        'product' => 'পণ্যের নাম',
        'variant' => 'ভ্যারিয়েন্ট',
        'sku' => 'SKU',
        'quantity' => 'বর্তমান স্টক',
        'reserved_quantity' => 'সংরক্ষিত স্টক',
        'available_quantity' => 'উপলব্ধ স্টক',
        'low_stock_threshold' => 'কম স্টকের সীমা',
        'allow_backorder' => 'ব্যাকঅর্ডার অনুমতি',
        'status' => 'স্ট্যাটাস',
        'last_updated' => 'শেষ আপডেট',
        'date' => 'তারিখ',
        'type' => 'ধরন',
        'amount' => 'পরিমাণ',
        'reason_note' => 'কারণ',
        'user' => 'ব্যবহারকারী',
        'stock_before' => 'আগের স্টক',
        'stock_after' => 'পরবর্তী স্টক',
    ],

    'actions' => [
        'add_stock' => 'স্টক যোগ করুন',
        'adjust_stock' => 'স্টক সমন্বয় করুন',
        'view_details' => 'বিস্তারিত দেখুন',
        'save_adjustment' => 'সমন্বয় সংরক্ষণ করুন',
    ],

    'forms' => [
        'add_title' => 'স্টক যোগ করুন',
        'adjust_title' => 'স্টক সমন্বয় করুন',
        'add_amount' => 'যোগ করার পরিমাণ',
        'adjustment_amount' => 'সমন্বয়ের পরিমাণ',
        'adjustment_hint' => 'স্টক বাড়াতে ধনাত্মক (যেমন: ২০), কমাতে ঋণাত্মক (যেমন: -৫) সংখ্যা দিন।',
        'note' => 'নোট',
        'reason' => 'কারণ',
        'current_stock' => 'বর্তমান স্টক',
        'save' => 'স্টক যোগ করুন',
    ],

    'history' => [
        'title' => 'লেনদেন ইতিহাস',
        'recent_activity' => 'সাম্প্রতিক কার্যক্রম',
        'empty' => 'কোনো লেনদেন নেই।',
    ],

    'filters' => [
        'all' => 'সব',
        'search_placeholder' => 'পণ্য, ভ্যারিয়েন্ট বা SKU দিয়ে খুঁজুন...',
        'filter' => 'ফিল্টার করুন',
    ],

    'messages' => [
        'stock_added' => 'স্টক সফলভাবে যোগ করা হয়েছে।',
        'stock_adjusted' => 'স্টক সফলভাবে সমন্বয় করা হয়েছে।',
        'no_inventory' => 'কোনো ইনভেন্টরি রেকর্ড পাওয়া যায়নি।',
    ],

    'errors' => [
        'insufficient_stock' => 'অপর্যাপ্ত স্টক। উপলব্ধ আছে মাত্র :availableটি।',
        'negative_stock' => 'স্টক ঋণাত্মক হতে পারবে না।',
        'negative_reserved' => 'সংরক্ষিত স্টক ঋণাত্মক হতে পারবে না।',
        'reserved_exceeds_stock' => 'সংরক্ষিত স্টক বর্তমান স্টকের বেশি হতে পারবে না।',
        'release_exceeds_reserved' => 'সংরক্ষিত পরিমাণের চেয়ে বেশি ছাড়া যাবে না।',
        'quantity_positive' => 'পরিমাণ শূন্যের বেশি হতে হবে।',
        'zero_adjustment' => 'সমন্বয়ের পরিমাণ শূন্য হতে পারবে না।',
    ],

    'notes' => [
        'return_restock' => 'ফেরত পণ্য বিক্রয়যোগ্য হিসেবে স্টকে যোগ হয়েছে।',
    ],
];
