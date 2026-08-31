<?php

namespace App\Support;

/**
 * অ্যাডমিন প্যানেলের সকল permission-এর একক উৎস।
 * এখানে নতুন permission যোগ করলে RoleSeeder (admin roles) স্বয়ংক্রিয়ভাবে
 * সুপার অ্যাডমিন/অ্যাডমিন-এ পাবে, এবং Roles UI-তেও দেখা যাবে।
 */
class AdminPermissions
{
    public const DASHBOARD = 'view-dashboard';

    // পণ্য
    public const PRODUCT_VIEW = 'products.view';
    public const PRODUCT_CREATE = 'products.create';
    public const PRODUCT_EDIT = 'products.edit';
    public const PRODUCT_DELETE = 'products.delete';

    // ক্যাটাগরি
    public const CATEGORY_VIEW = 'categories.view';
    public const CATEGORY_CREATE = 'categories.create';
    public const CATEGORY_EDIT = 'categories.edit';
    public const CATEGORY_DELETE = 'categories.delete';

    // ভ্যারিয়েন্ট
    public const VARIANT_VIEW = 'products.variants.view';
    public const VARIANT_CREATE = 'products.variants.create';
    public const VARIANT_EDIT = 'products.variants.edit';
    public const VARIANT_DELETE = 'products.variants.delete';

    // ইনভেন্টরি
    public const INVENTORY_VIEW = 'inventory.view';
    public const INVENTORY_CREATE = 'inventory.create';
    public const INVENTORY_ADJUST = 'inventory.adjust';
    public const INVENTORY_DELETE = 'inventory.delete';
    public const INVENTORY_HISTORY = 'inventory.history.view';

    // অর্ডার
    public const ORDER_VIEW = 'orders.view';
    public const ORDER_CREATE = 'orders.create';
    public const ORDER_UPDATE = 'orders.update';
    public const ORDER_DELETE = 'orders.delete';

    // ক্রেতা
    public const CUSTOMER_VIEW = 'customers.view';
    public const CUSTOMER_UPDATE = 'customers.update';

    // কুপন ও মার্কেটিং
    public const COUPON_VIEW = 'coupons.view';
    public const COUPON_CREATE = 'coupons.create';
    public const COUPON_UPDATE = 'coupons.update';
    public const COUPON_DELETE = 'coupons.delete';

    // অফার
    public const OFFER_VIEW = 'offers.view';
    public const OFFER_CREATE = 'offers.create';
    public const OFFER_UPDATE = 'offers.update';
    public const OFFER_DELETE = 'offers.delete';

    // কনটেন্ট / হোমপেজ
    public const CONTENT_MANAGE = 'content.manage';

    // কৃষক / সরবরাহকারী
    public const FARMER_VIEW = 'farmers.view';
    public const FARMER_CREATE = 'farmers.create';
    public const FARMER_UPDATE = 'farmers.update';
    public const FARMER_DELETE = 'farmers.delete';

    // ডেলিভারি
    public const DELIVERY_MANAGE = 'delivery.manage';

    // পেমেন্ট
    public const PAYMENT_MANAGE = 'payments.manage';

    // রিপোর্ট
    public const REPORT_VIEW = 'reports.view';

    // মিডিয়া
    public const MEDIA_VIEW = 'media.view';
    public const MEDIA_UPLOAD = 'media.upload';
    public const MEDIA_DELETE = 'media.delete';

    // স্টাফ / সিস্টেম
    public const STAFF_MANAGE = 'staff.manage';
    public const SETTINGS_VIEW = 'settings.view';
    public const SETTINGS_UPDATE = 'settings.update';
    public const ACTIVITY_VIEW = 'activity.view';

    // ---------- কম্বল ----------

    /**
     * সকল permission-এর তালিকা (পাঠযোগ্য দলে বিন্যস্ত)।
     *
     * @return array<string, array<int, string>>
     */
    public static function grouped(): array
    {
        return [
            'ড্যাশবোর্ড' => [self::DASHBOARD],
            'পণ্য' => [self::PRODUCT_VIEW, self::PRODUCT_CREATE, self::PRODUCT_EDIT, self::PRODUCT_DELETE],
            'ক্যাটাগরি' => [self::CATEGORY_VIEW, self::CATEGORY_CREATE, self::CATEGORY_EDIT, self::CATEGORY_DELETE],
            'ভ্যারিয়েন্ট' => [self::VARIANT_VIEW, self::VARIANT_CREATE, self::VARIANT_EDIT, self::VARIANT_DELETE],
            'ইনভেন্টরি' => [self::INVENTORY_VIEW, self::INVENTORY_CREATE, self::INVENTORY_ADJUST, self::INVENTORY_DELETE, self::INVENTORY_HISTORY],
            'অর্ডার' => [self::ORDER_VIEW, self::ORDER_CREATE, self::ORDER_UPDATE, self::ORDER_DELETE],
            'ক্রেতা' => [self::CUSTOMER_VIEW, self::CUSTOMER_UPDATE],
            'কুপন' => [self::COUPON_VIEW, self::COUPON_CREATE, self::COUPON_UPDATE, self::COUPON_DELETE],
            'অফার' => [self::OFFER_VIEW, self::OFFER_CREATE, self::OFFER_UPDATE, self::OFFER_DELETE],
            'কনটেন্ট / হোমপেজ' => [self::CONTENT_MANAGE],
            'কৃষক' => [self::FARMER_VIEW, self::FARMER_CREATE, self::FARMER_UPDATE, self::FARMER_DELETE],
            'ডেলিভারি' => [self::DELIVERY_MANAGE],
            'পেমেন্ট' => [self::PAYMENT_MANAGE],
            'রিপোর্ট' => [self::REPORT_VIEW],
            'মিডিয়া' => [self::MEDIA_VIEW, self::MEDIA_UPLOAD, self::MEDIA_DELETE],
            'স্টাফ ও সিস্টেম' => [self::STAFF_MANAGE, self::SETTINGS_VIEW, self::SETTINGS_UPDATE, self::ACTIVITY_VIEW],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_merge(...array_values(self::grouped()));
    }
}
