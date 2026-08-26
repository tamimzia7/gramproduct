<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('inventory.view');
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('inventory.view');
    }

    /**
     * স্টক যোগ (restock/purchase)
     */
    public function addStock(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('inventory.create');
    }

    /**
     * স্টক সমন্বয় / ক্ষতিগ্রস্ত-মেয়াদোত্তীর্ণ অপসারণ
     */
    public function adjust(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('inventory.adjust');
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('inventory.delete');
    }

    /**
     * লেনদেন ইতিহাস দেখা
     */
    public function viewHistory(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('inventory.history.view')
            || $user->hasPermission('inventory.view');
    }
}
