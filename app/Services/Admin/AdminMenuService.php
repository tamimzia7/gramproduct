<?php

namespace App\Services\Admin;

use App\Models\User;

/**
 * অ্যাডমিন সাইডবার নেভিগেশন — permission-সচেতন।
 *
 * প্রতিটি মেনু আইটেমের `permission` (অথবা `any_of`) দিয়ে অ্যাডমিনের
 * অধিকার যাচাই করা হয়। "Super Admin" সব দেখবে (hasPermission সুপার অ্যাডমিনকে
 * সব permit করে)। Backend authorization-ও প্রতিটি route-এ mandatory — শুধু
 * UI লুকানো নয়।
 */
class AdminMenuService
{
    /**
     * সাইডবার গ্রুপ/আইটেম বিন্যাস — Bengali-first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function groups(): array
    {
        return [
            [
                'label' => 'ড্যাশবোর্ড',
                'items' => [
                    ['label' => 'ড্যাশবোর্ড', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard', 'permission' => null],
                ],
            ],
            [
                'label' => 'ক্যাটালগ',
                'items' => [
                    ['label' => 'পণ্য', 'icon' => 'bi-box-seam', 'route' => 'admin.products.index', 'permission' => 'products.view'],
                    ['label' => 'ক্যাটাগরি', 'icon' => 'bi-diagram-3', 'route' => 'admin.categories.index', 'permission' => 'categories.view'],
                    ['label' => 'ভ্যারিয়েন্ট', 'icon' => 'bi-collection', 'route' => 'admin.variants.index', 'permission' => 'products.variants.view'],
                    ['label' => 'ইনভেন্টরি ও স্টক', 'icon' => 'bi-clipboard-data', 'route' => 'admin.inventory.index', 'permission' => 'inventory.view'],
                ],
            ],
            [
                'label' => 'অর্ডার',
                'items' => [
                    ['label' => 'সব অর্ডার', 'icon' => 'bi-receipt', 'route' => 'admin.orders.index', 'permission' => 'orders.view'],
                    ['label' => 'নতুন', 'icon' => 'bi-inbox', 'route' => 'admin.orders.index', 'params' => ['status' => 'pending'], 'permission' => 'orders.view'],
                    ['label' => 'প্রক্রিয়াধীন', 'icon' => 'bi-gear', 'route' => 'admin.orders.index', 'params' => ['status' => 'processing'], 'permission' => 'orders.view'],
                    ['label' => 'পাঠানো হয়েছে', 'icon' => 'bi-truck', 'route' => 'admin.orders.index', 'params' => ['status' => 'shipped'], 'permission' => 'orders.view'],
                    ['label' => 'সম্পন্ন', 'icon' => 'bi-check2-circle', 'route' => 'admin.orders.index', 'params' => ['status' => 'completed'], 'permission' => 'orders.view'],
                    ['label' => 'বাতিল', 'icon' => 'bi-x-circle', 'route' => 'admin.orders.index', 'params' => ['status' => 'cancelled'], 'permission' => 'orders.view'],
                ],
            ],
            [
                'label' => 'ক্রেতা',
                'items' => [
                    ['label' => 'সব ক্রেতা', 'icon' => 'bi-people', 'route' => 'admin.customers.index', 'permission' => 'customers.view'],
                ],
            ],
            [
                'label' => 'মার্কেটিং',
                'items' => [
                    ['label' => 'কুপন', 'icon' => 'bi-ticket-perforated', 'route' => 'admin.coupons.index', 'permission' => 'coupons.view'],
                    ['label' => 'বিশেষ অফার', 'icon' => 'bi-stars', 'route' => 'admin.offers.index', 'permission' => 'offers.view'],
                    ['label' => 'ব্যানার', 'icon' => 'bi-images', 'route' => 'admin.banners.index', 'permission' => 'content.manage'],
                ],
            ],
            [
                'label' => 'হোমপেজ',
                'items' => [
                    ['label' => 'Hero স্লাইড', 'icon' => 'bi-window', 'route' => 'admin.hero.index', 'permission' => 'content.manage'],
                    ['label' => 'সেকশন', 'icon' => 'bi-layout-three-columns', 'route' => 'admin.homepage.index', 'permission' => 'content.manage'],
                    ['label' => 'কেন আমাদের কাছ থেকে', 'icon' => 'bi-patch-check', 'route' => 'admin.homepage.why', 'permission' => 'content.manage'],
                    ['label' => 'আমাদের গল্প', 'icon' => 'bi-book', 'route' => 'admin.story.index', 'permission' => 'content.manage'],
                    ['label' => 'ক্রেতার মতামত', 'icon' => 'bi-chat-quote', 'route' => 'admin.testimonials.index', 'permission' => 'content.manage'],
                    ['label' => 'কৃষক', 'icon' => 'bi-person-arms-up', 'route' => 'admin.farmers.index', 'permission' => 'farmers.view'],
                ],
            ],
            [
                'label' => 'কনটেন্ট',
                'items' => [
                    ['label' => 'ব্লগ', 'icon' => 'bi-journal-text', 'route' => 'admin.blogs.index', 'permission' => 'content.manage'],
                    ['label' => 'পেজ', 'icon' => 'bi-file-earmark-text', 'route' => 'admin.pages.index', 'permission' => 'content.manage'],
                    ['label' => 'মিডিয়া লাইব্রেরি', 'icon' => 'bi-collection-play', 'route' => 'admin.media.index', 'permission' => 'media.view'],
                ],
            ],
            [
                'label' => 'ডেলিভারি ও পেমেন্ট',
                'items' => [
                    ['label' => 'ডেলিভারি এলাকা', 'icon' => 'bi-geo-alt', 'route' => 'admin.delivery.index', 'permission' => 'delivery.manage'],
                    ['label' => 'ডেলিভারি চার্জ', 'icon' => 'bi-cash-coin', 'route' => 'admin.delivery.charges', 'permission' => 'delivery.manage'],
                    ['label' => 'পেমেন্ট পদ্ধতি', 'icon' => 'bi-credit-card-2-front', 'route' => 'admin.payments.index', 'permission' => 'payments.manage'],
                ],
            ],
            [
                'label' => 'রিপোর্ট',
                'items' => [
                    ['label' => 'বিক্রয় রিপোর্ট', 'icon' => 'bi-bar-chart-line', 'route' => 'admin.reports.sales', 'permission' => 'reports.view'],
                    ['label' => 'অর্ডার রিপোর্ট', 'icon' => 'bi-receipt-cutoff', 'route' => 'admin.reports.orders', 'permission' => 'reports.view'],
                    ['label' => 'পণ্য রিপোর্ট', 'icon' => 'bi-box', 'route' => 'admin.reports.products', 'permission' => 'reports.view'],
                    ['label' => 'ক্যাটাগরি রিপোর্ট', 'icon' => 'bi-diagram-2', 'route' => 'admin.reports.categories', 'permission' => 'reports.view'],
                    ['label' => 'ক্রেতা রিপোর্ট', 'icon' => 'bi-people', 'route' => 'admin.reports.customers', 'permission' => 'reports.view'],
                    ['label' => 'কুপন রিপোর্ট', 'icon' => 'bi-ticket', 'route' => 'admin.reports.coupons', 'permission' => 'reports.view'],
                    ['label' => 'ইনভেন্টরি রিপোর্ট', 'icon' => 'bi-clipboard-check', 'route' => 'admin.reports.inventory', 'permission' => 'reports.view'],
                ],
            ],
            [
                'label' => 'সিস্টেম',
                'items' => [
                    ['label' => 'অ্যাডমিন ইউজার', 'icon' => 'bi-person-badge', 'route' => 'admin.users.index', 'permission' => 'staff.manage'],
                    ['label' => 'Roles ও Permissions', 'icon' => 'bi-shield-lock', 'route' => 'admin.roles.index', 'permission' => 'staff.manage'],
                    ['label' => 'সেটিংস', 'icon' => 'bi-sliders', 'route' => 'admin.settings.index', 'permission' => 'settings.view'],
                    ['label' => 'অ্যাক্টিভিটি লগ', 'icon' => 'bi-activity', 'route' => 'admin.activity-log.index', 'permission' => 'activity.view'],
                ],
            ],
        ];
    }

    /**
     * বর্তমান অ্যাডমিনের জন্য দৃশ্যমান মেনু গ্রুপগুলোর তালিকা।
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $groups = [];

        foreach ($this->groups() as $group) {
            $visibleItems = array_values(array_filter(
                $group['items'],
                fn (array $item): bool => $this->canSee($user, $item),
            ));

            if ($visibleItems !== []) {
                $groups[] = [
                    'label' => $group['label'],
                    'items' => $visibleItems,
                ];
            }
        }

        return $groups;
    }

    /**
     * কোনো আইটেম দেখানোর অনুমতি আছে কি না।
     *
     * @param  array<string, mixed>  $item
     */
    private function canSee(User $user, array $item): bool
    {
        $permission = $item['permission'] ?? null;

        // permission সেট না থাকলে (যেমন dashboard) সবাই দেখে
        if ($permission === null) {
            return true;
        }

        return $user->hasPermission($permission);
    }
}
