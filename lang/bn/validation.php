<?php

return [

    /* |--------------------------------------------------------------------------
    | | ভাষার লাইন
    | |--------------------------------------------------------------------------
    | |
    | | নিচের ভাষার লাইনগুলো ভ্যালিডেটর ক্লাস দ্বারা ব্যবহৃত হয়। এই লাইনগুলো
    | | আপনার অ্যাপ্লিকেশনের বিভিন্ন যাচাইকরণ ত্রুটির বার্তা সংরক্ষণ করে।
    | |
    */

    'accepted' => ':attribute গ্রহণ করতে হবে।',
    'accepted_if' => 'যখন :other-এর মান :value হয়, তখন :attribute গ্রহণ করতে হবে।',
    'active_url' => ':attribute একটি বৈধ URL নয়।',
    'after' => ':attribute অবশ্যই :date-এর পরের একটি তারিখ হতে হবে।',
    'after_or_equal' => ':attribute অবশ্যই :date-এর সমান বা পরের একটি তারিখ হতে হবে।',
    'alpha' => ':attribute-এ শুধুমাত্র অক্ষর থাকতে পারে।',
    'alpha_dash' => ':attribute-এ শুধুমাত্র অক্ষর, সংখ্যা, ড্যাশ ও আন্ডারস্কোর থাকতে পারে।',
    'alpha_num' => ':attribute-এ শুধুমাত্র অক্ষর ও সংখ্যা থাকতে পারে।',
    'array' => ':attribute অবশ্যই একটি অ্যারে হতে হবে।',
    'ascii' => ':attribute-এ শুধুমাত্র একক-বাইট বর্ণসংকেত অক্ষর ও প্রতীক থাকতে পারে।',
    'before' => ':attribute অবশ্যই :date-এর আগের একটি তারিখ হতে হবে।',
    'before_or_equal' => ':attribute অবশ্যই :date-এর সমান বা আগের একটি তারিখ হতে হবে।',
    'between' => [
        'array' => ':attribute-এ :min থেকে :max সংখ্যক আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :min থেকে :max কিলোবাইটের মধ্যে হতে হবে।',
        'numeric' => ':attribute অবশ্যই :min থেকে :max-এর মধ্যে হতে হবে।',
        'string' => ':attribute অবশ্যই :min থেকে :max অক্ষরের মধ্যে হতে হবে।',
    ],
    'boolean' => ':attribute ফিল্ডটি অবশ্যই সত্য বা মিথ্যা হতে হবে।',
    'can' => ':attribute ফিল্ডে অননুমোদিত মান রয়েছে।',
    'confirmed' => ':attribute নিশ্চিতকরণের সাথে মিলছে না।',
    'contains' => ':attribute ফিল্ডে প্রয়োজনীয় মান নেই।',
    'current_password' => 'পাসওয়ার্ডটি সঠিক নয়।',
    'date' => ':attribute একটি বৈধ তারিখ নয়।',
    'date_equals' => ':attribute অবশ্যই :date-এর সমান একটি তারিখ হতে হবে।',
    'date_format' => ':attribute ফরম্যাট :format-এর সাথে মিলছে না।',
    'decimal' => ':attribute-এ :decimal সংখ্যক দশমিক স্থান থাকতে হবে।',
    'declined' => ':attribute প্রত্যাখ্যান করতে হবে।',
    'declined_if' => 'যখন :other-এর মান :value হয়, তখন :attribute প্রত্যাখ্যান করতে হবে।',
    'different' => ':attribute এবং :other অবশ্যই ভিন্ন হতে হবে।',
    'digits' => ':attribute অবশ্যই :digits সংখ্যক ডিজিটের হতে হবে।',
    'digits_between' => ':attribute অবশ্যই :min থেকে :max সংখ্যক ডিজিটের মধ্যে হতে হবে।',
    'dimensions' => ':attribute-এর ছবির মাত্রা সঠিক নয়।',
    'distinct' => ':attribute ফিল্ডে ডুপ্লিকেট মান রয়েছে।',
    'doesnt_end_with' => ':attribute নিম্নলিখিতগুলোর একটি দিয়ে শেষ হতে পারবে না: :values।',
    'doesnt_start_with' => ':attribute নিম্নলিখিতগুলোর একটি দিয়ে শুরু হতে পারবে না: :values।',
    'email' => ':attribute অবশ্যই একটি বৈধ ইমেইল ঠিকানা হতে হবে।',
    'ends_with' => ':attribute নিম্নলিখিতগুলোর একটি দিয়ে শেষ হতে হবে: :values।',
    'enum' => 'নির্বাচিত :attribute সঠিক নয়।',
    'exists' => 'নির্বাচিত :attribute সঠিক নয়।',
    'extensions' => ':attribute ফিল্ডে নিম্নলিখিত এক্সটেনশনগুলোর একটি থাকতে হবে: :values।',
    'file' => ':attribute অবশ্যই একটি ফাইল হতে হবে।',
    'filled' => ':attribute ফিল্ডে অবশ্যই কোনো মান থাকতে হবে।',
    'gt' => [
        'array' => ':attribute-এ :value-এর চেয়ে বেশি সংখ্যক আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের চেয়ে বড় হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value-এর চেয়ে বড় হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের চেয়ে বড় হতে হবে।',
    ],
    'gte' => [
        'array' => ':attribute-এ :value বা তার বেশি সংখ্যক আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইট বা তার চেয়ে বড় হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value বা তার চেয়ে বড় হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষর বা তার চেয়ে বড় হতে হবে।',
    ],
    'hex_color' => ':attribute ফিল্ডটি অবশ্যই একটি বৈধ hex রঙের কোড হতে হবে।',
    'image' => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'in_array' => ':attribute ফিল্ডটি :other-এ নেই।',
    'integer' => ':attribute অবশ্যই একটি পূর্ণসংখ্যা হতে হবে।',
    'ip' => ':attribute অবশ্যই একটি বৈধ IP ঠিকানা হতে হবে।',
    'ipv4' => ':attribute অবশ্যই একটি বৈধ IPv4 ঠিকানা হতে হবে।',
    'ipv6' => ':attribute অবশ্যই একটি বৈধ IPv6 ঠিকানা হতে হবে।',
    'json' => ':attribute অবশ্যই একটি বৈধ JSON স্ট্রিং হতে হবে।',
    'list' => ':attribute ফিল্ডটি অবশ্যই একটি তালিকা হতে হবে।',
    'lowercase' => ':attribute অবশ্যই ছোট হাতের অক্ষরে হতে হবে।',
    'lt' => [
        'array' => ':attribute-এ :value-এর চেয়ে কম সংখ্যক আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের চেয়ে ছোট হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value-এর চেয়ে ছোট হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের চেয়ে ছোট হতে হবে।',
    ],
    'lte' => [
        'array' => ':attribute-এ :value-এর বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute অবশ্যই :value কিলোবাইট বা তার চেয়ে ছোট হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value বা তার চেয়ে ছোট হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষর বা তার চেয়ে ছোট হতে হবে।',
    ],
    'mac_address' => ':attribute অবশ্যই একটি বৈধ MAC ঠিকানা হতে হবে।',
    'max' => [
        'array' => ':attribute-এ সর্বাধিক :max সংখ্যক আইটেম থাকতে পারবে।',
        'file' => ':attribute সর্বোচ্চ :max কিলোবাইট হতে পারবে।',
        'numeric' => ':attribute সর্বোচ্চ :max হতে পারবে।',
        'string' => ':attribute সর্বাধিক :max অক্ষরের হতে পারবে।',
    ],
    'max_digits' => ':attribute-এ সর্বাধিক :max সংখ্যক ডিজিট থাকতে পারবে।',
    'mimes' => ':attribute অবশ্যই নিম্নলিখিত ধরনের ফাইল হতে হবে: :values।',
    'mimetypes' => ':attribute অবশ্যই নিম্নলিখিত ধরনের ফাইল হতে হবে: :values।',
    'min' => [
        'array' => ':attribute-এ কমপক্ষে :min সংখ্যক আইটেম থাকতে হবে।',
        'file' => ':attribute কমপক্ষে :min কিলোবাইট হতে হবে।',
        'numeric' => ':attribute কমপক্ষে :min হতে হবে।',
        'string' => ':attribute কমপক্ষে :min অক্ষরের হতে হবে।',
    ],
    'min_digits' => ':attribute-এ কমপক্ষে :min সংখ্যক ডিজিট থাকতে হবে।',
    'missing' => ':attribute ফিল্ডটি থাকতে পারবে না।',
    'missing_if' => 'যখন :other-এর মান :value হয়, তখন :attribute থাকতে পারবে না।',
    'missing_unless' => 'যতক্ষণ না :other-এর মান :value হয়, ততক্ষণ :attribute থাকতে পারবে না।',
    'missing_with' => ':values উপস্থিত থাকলে :attribute থাকতে পারবে না।',
    'missing_with_all' => ':values উপস্থিত থাকলে :attribute থাকতে পারবে না।',
    'multiple_of' => ':attribute অবশ্যই :value-এর গুণিতক হতে হবে।',
    'not_in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'not_regex' => ':attribute ফরম্যাট সঠিক নয়।',
    'numeric' => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'password' => [
        'letters' => ':attribute-এ কমপক্ষে একটি অক্ষর থাকতে হবে।',
        'mixed' => ':attribute-এ অন্তত একটি বড় হাতের ও একটি ছোট হাতের অক্ষর থাকতে হবে।',
        'numbers' => ':attribute-এ কমপক্ষে একটি সংখ্যা থাকতে হবে।',
        'symbols' => ':attribute-এ কমপক্ষে একটি প্রতীক থাকতে হবে।',
        'uncompromised' => 'প্রদত্ত :attribute একটি ডেটা লিকে আবির্ভূত হয়েছে। অনুগ্রহ করে ভিন্ন :attribute চয়েস করুন।',
    ],
    'present' => ':attribute ফিল্ডটি অবশ্যই উপস্থিত থাকতে হবে।',
    'present_if' => 'যখন :other-এর মান :value হয়, তখন :attribute উপস্থিত থাকতে হবে।',
    'present_unless' => 'যতক্ষণ না :other-এর মান :value হয়, ততক্ষণ :attribute উপস্থিত থাকতে হবে।',
    'present_with' => ':values উপস্থিত থাকলে :attribute উপস্থিত থাকতে হবে।',
    'present_with_all' => ':values উপস্থিত থাকলে :attribute উপস্থিত থাকতে হবে।',
    'prohibited' => ':attribute ফিল্ডটি নিষিদ্ধ।',
    'prohibited_if' => 'যখন :other-এর মান :value হয়, তখন :attribute নিষিদ্ধ।',
    'prohibited_unless' => 'যতক্ষণ না :other-এর মান :values এর মধ্যে থাকে, ততক্ষণ :attribute নিষিদ্ধ।',
    'prohibits' => ':attribute ফিল্ড :other-কে উপস্থিত হতে দেয় না।',
    'regex' => ':attribute ফরম্যাট সঠিক নয়।',
    'required' => ':attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_array_keys' => ':attribute ফিল্ডে অবশ্যই নিম্নলিখিত কীগুলো থাকতে হবে: :values।',
    'required_if' => 'যখন :other-এর মান :value হয়, তখন :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_if_accepted' => 'যখন :other গ্রহণ করা হয়, তখন :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_if_declined' => 'যখন :other প্রত্যাখ্যান করা হয়, তখন :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_unless' => ':other-এর মান :values না হলে :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_with' => ':values উপস্থিত থাকলে :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_with_all' => ':values উপস্থিত থাকলে :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_without' => ':values উপস্থিত না থাকলে :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'required_without_all' => ':values কোনোটি উপস্থিত না থাকলে :attribute ফিল্ডটি অবশ্যই দিতে হবে।',
    'same' => ':attribute এবং :other অবশ্যই একই হতে হবে।',
    'size' => [
        'array' => ':attribute-এ ঠিক :size সংখ্যক আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :size কিলোবাইটের হতে হবে।',
        'numeric' => ':attribute অবশ্যই :size হতে হবে।',
        'string' => ':attribute অবশ্যই :size অক্ষরের হতে হবে।',
    ],
    'starts_with' => ':attribute নিম্নলিখিতগুলোর একটি দিয়ে শুরু হতে হবে: :values।',
    'string' => ':attribute অবশ্যই একটি স্ট্রিং হতে হবে।',
    'timezone' => ':attribute অবশ্যই একটি বৈধ টাইমজোন হতে হবে।',
    'unique' => ':attribute ইতিমধ্যে ব্যবহৃত হয়েছে।',
    'uploaded' => ':attribute আপলোড করা যায়নি।',
    'uppercase' => ':attribute অবশ্যই বড় হাতের অক্ষরে হতে হবে।',
    'url' => ':attribute অবশ্যই একটি বৈধ URL হতে হবে।',
    'ulid' => ':attribute অবশ্যই একটি বৈধ ULID হতে হবে।',
    'uuid' => ':attribute অবশ্যই একটি বৈধ UUID হতে হবে।',

    /* |--------------------------------------------------------------------------
    | | কাস্টম ভ্যালিডেশন ভাষার লাইন
    | |--------------------------------------------------------------------------
    | |
    | | এখানে "attributes" ব্যবহার করে কাস্টম ভ্যালিডেশন মেসেজ নির্দিষ্ট করতে
    | | পারেন, যা আপনার অ্যাপ্লিকেশনের প্রয়োজন অনুযায়ী ব্যবহৃত হবে।
    | |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /* |--------------------------------------------------------------------------
    | | কাস্টম ভ্যালিডেশন অ্যাট্রিবিউট
    | |--------------------------------------------------------------------------
    | |
    | | নিচের ভাষার লাইনগুলো ভাষার লাইনে placeholder প্রতিস্থাপনের জন্য
    | | ব্যবহৃত হয়, যেখানে অ্যাট্রিবিউটের নাম আরও পাঠযোগ্যভাবে উপস্থাপন করা হয়।
    | |
    */

    'attributes' => [
        'name' => 'নাম',
        'email' => 'ইমেইল ঠিকানা',
        'password' => 'পাসওয়ার্ড',
        'phone' => 'ফোন নম্বর',
        'description' => 'বিবরণ',
        'image' => 'ছবি',
        'price' => 'মূল্য',
        'quantity' => 'পরিমাণ',
        'address' => 'ঠিকানা',
        'city' => 'শহর',
        'postal_code' => 'পোস্ট কোড',
    ],

];
