<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * সকল permission — RoleSeeder-এর একমাত্র উৎস।
     * অ্যাডমিন/সুপার অ্যাডমিন উভয়েই সব permission পায়।
     *
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return AdminPermissions::all();
    }

    public function run(): void
    {
        $allPermissions = self::permissions();

        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full access to the entire platform.',
                'permissions' => $allPermissions,
                'is_system' => true,
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Business management across all modules.',
                'permissions' => $allPermissions,
                'is_system' => true,
            ],
            'product-manager' => [
                'name' => 'Product Manager',
                'description' => 'Manages products and categories.',
                'permissions' => [
                    AdminPermissions::DASHBOARD,
                    AdminPermissions::PRODUCT_VIEW,
                    AdminPermissions::PRODUCT_CREATE,
                    AdminPermissions::PRODUCT_EDIT,
                    AdminPermissions::PRODUCT_DELETE,
                    AdminPermissions::CATEGORY_VIEW,
                    AdminPermissions::CATEGORY_CREATE,
                    AdminPermissions::CATEGORY_EDIT,
                    AdminPermissions::CATEGORY_DELETE,
                    AdminPermissions::VARIANT_VIEW,
                    AdminPermissions::VARIANT_CREATE,
                    AdminPermissions::VARIANT_EDIT,
                    AdminPermissions::VARIANT_DELETE,
                    AdminPermissions::REPORT_VIEW,
                    'manage-products',
                    'manage-categories',
                ],
                'is_system' => true,
            ],
            'order-manager' => [
                'name' => 'Order Manager',
                'description' => 'Manages customer orders.',
                'permissions' => [
                    AdminPermissions::DASHBOARD,
                    AdminPermissions::ORDER_VIEW,
                    AdminPermissions::ORDER_CREATE,
                    AdminPermissions::ORDER_UPDATE,
                    AdminPermissions::ORDER_DELETE,
                    AdminPermissions::CUSTOMER_VIEW,
                    AdminPermissions::REPORT_VIEW,
                    'manage-orders',
                ],
                'is_system' => true,
            ],
            'inventory-manager' => [
                'name' => 'Inventory Manager',
                'description' => 'Manages stock and inventory.',
                'permissions' => [
                    AdminPermissions::DASHBOARD,
                    AdminPermissions::INVENTORY_VIEW,
                    AdminPermissions::INVENTORY_CREATE,
                    AdminPermissions::INVENTORY_ADJUST,
                    AdminPermissions::INVENTORY_DELETE,
                    AdminPermissions::INVENTORY_HISTORY,
                    AdminPermissions::REPORT_VIEW,
                    'manage-inventory',
                ],
                'is_system' => true,
            ],
            'content-manager' => [
                'name' => 'Content Manager',
                'description' => 'Manages blog, pages, testimonials and homepage content.',
                'permissions' => [
                    AdminPermissions::DASHBOARD,
                    AdminPermissions::CONTENT_MANAGE,
                    AdminPermissions::MEDIA_VIEW,
                    AdminPermissions::MEDIA_UPLOAD,
                    'manage-content',
                ],
                'is_system' => true,
            ],
            'delivery-manager' => [
                'name' => 'Delivery Manager',
                'description' => 'Manages delivery and shipping.',
                'permissions' => [
                    AdminPermissions::DASHBOARD,
                    AdminPermissions::DELIVERY_MANAGE,
                    AdminPermissions::ORDER_VIEW,
                    AdminPermissions::ORDER_UPDATE,
                    AdminPermissions::REPORT_VIEW,
                    'manage-delivery',
                ],
                'is_system' => true,
            ],
            'report-viewer' => [
                'name' => 'Report Viewer',
                'description' => 'Read-only access to reports and dashboard.',
                'permissions' => [
                    AdminPermissions::DASHBOARD,
                    AdminPermissions::REPORT_VIEW,
                    'view-reports',
                ],
                'is_system' => true,
            ],
        ];

        foreach ($roles as $slug => $data) {
            Role::updateOrCreate(['slug' => $slug], [
                'name' => $data['name'],
                'description' => $data['description'],
                'permissions' => $data['permissions'],
                'is_system' => $data['is_system'],
            ]);
        }
    }
}
