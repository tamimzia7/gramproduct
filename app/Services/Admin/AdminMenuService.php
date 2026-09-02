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
                'label' => 'পণ্য ও ক্যাটালগ',
                'items' => [
                    ['label' => 'পণ্য', 'icon' => 'bi-box-seam', 'route' => 'admin.products.index', 'permission' => 'products.view'],
                    ['label' => 'চালের ক্যাটাগরি', 'icon' => 'bi-rice', 'route' => 'admin.categories.index', 'params' => ['type' => 'rice'], 'permission' => 'categories.view'],
                    ['label' => 'সকল ক্যাটাগরি', 'icon' => 'bi-diagram-3', 'route' => 'admin.categories.index', 'permission' => 'categories.view'],
                ],
            ],
            [
                'label' => 'মজুদ ও অর্ডার',
                'items' => [
                    ['label' => 'মজুদ ব্যবস্থাপনা', 'icon' => 'bi-clipboard-data', 'route' => 'admin.inventory.index', 'permission' => 'inventory.view'],
                    ['label' => 'অর্ডার', 'icon' => 'bi-receipt', 'route' => 'admin.orders.index', 'permission' => 'orders.view'],
                ],
            ],
            [
                'label' => 'গ্রাহক ও বিপণন',
                'items' => [
                    ['label' => 'গ্রাহক', 'icon' => 'bi-people', 'route' => 'admin.customers.index', 'permission' => 'customers.view'],
                    ['label' => 'কুপন', 'icon' => 'bi-ticket-perforated', 'route' => 'admin.coupons.index', 'permission' => 'coupons.view'],
                    ['label' => 'কৃষক', 'icon' => 'bi-person-arms-up', 'route' => 'admin.farmers.index', 'permission' => 'farmers.view'],
                ],
            ],
            [
                'label' => 'ডেলিভারি ও পেমেন্ট',
                'items' => [
                    ['label' => 'ডেলিভারি', 'icon' => 'bi-truck', 'route' => 'admin.delivery.index', 'permission' => 'delivery.manage'],
                    ['label' => 'পেমেন্ট', 'icon' => 'bi-credit-card-2-front', 'route' => 'admin.payments.index', 'permission' => 'payments.manage'],
                ],
            ],
            [
                'label' => 'কনটেন্ট',
                'items' => [
                    ['label' => 'রিভিউ', 'icon' => 'bi-star', 'route' => 'admin.reviews.index', 'permission' => 'content.manage'],
                    ['label' => 'ব্লগ', 'icon' => 'bi-journal-text', 'route' => 'admin.blogs.index', 'permission' => 'content.manage'],
                    ['label' => 'রিপোর্ট', 'icon' => 'bi-bar-chart-line', 'route' => 'admin.reports.sales', 'permission' => 'reports.view'],
                    ['label' => 'ওয়েবসাইট কনটেন্ট', 'icon' => 'bi-globe', 'route' => 'admin.homepage.index', 'permission' => 'content.manage'],
                ],
            ],
            [
                'label' => 'সিস্টেম',
                'items' => [
                    ['label' => 'ব্যবহারকারী', 'icon' => 'bi-person-badge', 'route' => 'admin.users.index', 'permission' => 'staff.manage'],
                    ['label' => 'ভূমিকা ও অনুমতি', 'icon' => 'bi-shield-lock', 'route' => 'admin.roles.index', 'permission' => 'staff.manage'],
                    ['label' => 'সেটিংস', 'icon' => 'bi-sliders', 'route' => 'admin.settings.index', 'permission' => 'settings.view'],
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

        if ($permission === null) {
            return true;
        }

        return $user->hasPermission($permission);
    }
}
