<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ডেলিভারি কনফিগারেশন
    |--------------------------------------------------------------------------
    |
    | মেথড ও ফি এখানেই কেন্দ্রীভূত — Blade/controller-এ hard-code নয়।
    | Phase 09+ এ delivery_zones / district pricing / weight-based pricing
    | যোগ করা যাবে এই কাঠামোর ভেতরেই।
    |
    */

    'default_method' => 'home_delivery',

    'fees' => [
        'home_delivery' => 80,
    ],
];
