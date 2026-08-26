<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public const PERMISSIONS = [
        'view-dashboard',
        'manage-products',
        'manage-categories',
        'manage-orders',
        'manage-inventory',
        'manage-content',
        'manage-delivery',
        'manage-customers',
        'manage-staff',
        'manage-settings',
        'view-reports',
        // Product মডিউলের granular permission (ProductPolicy ব্যবহার করে)
        'products.view',
        'products.create',
        'products.edit',
        'products.delete',
        // ভ্যারিয়েন্ট মডিউলের granular permission (ProductVariantPolicy ব্যবহার করে)
        'products.variants.view',
        'products.variants.create',
        'products.variants.edit',
        'products.variants.delete',
        // ইনভেন্টরি মডিউলের granular permission (InventoryPolicy ব্যবহার করে)
        'inventory.view',
        'inventory.create',
        'inventory.adjust',
        'inventory.delete',
        'inventory.history.view',
    ];

    public function run(): void
    {
        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full access to the entire platform.',
                'permissions' => self::PERMISSIONS,
                'is_system' => true,
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Business management across all modules.',
                'permissions' => self::PERMISSIONS,
                'is_system' => true,
            ],
            'product-manager' => [
                'name' => 'Product Manager',
                'description' => 'Manages products and categories.',
                'permissions' => [
                    'view-dashboard',
                    'manage-products',
                    'manage-categories',
                    'view-reports',
                    'products.view',
                    'products.create',
                    'products.edit',
                    'products.delete',
                    'products.variants.view',
                    'products.variants.create',
                    'products.variants.edit',
                    'products.variants.delete',
                ],
                'is_system' => true,
            ],
            'order-manager' => [
                'name' => 'Order Manager',
                'description' => 'Manages customer orders.',
                'permissions' => ['view-dashboard', 'manage-orders', 'view-reports'],
                'is_system' => true,
            ],
            'inventory-manager' => [
                'name' => 'Inventory Manager',
                'description' => 'Manages stock and inventory.',
                'permissions' => [
                    'view-dashboard',
                    'manage-inventory',
                    'view-reports',
                    'inventory.view',
                    'inventory.create',
                    'inventory.adjust',
                    'inventory.delete',
                    'inventory.history.view',
                ],
                'is_system' => true,
            ],
            'content-manager' => [
                'name' => 'Content Manager',
                'description' => 'Manages blog and stories.',
                'permissions' => ['view-dashboard', 'manage-content'],
                'is_system' => true,
            ],
            'delivery-manager' => [
                'name' => 'Delivery Manager',
                'description' => 'Manages delivery and shipping.',
                'permissions' => ['view-dashboard', 'manage-delivery', 'view-reports'],
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
