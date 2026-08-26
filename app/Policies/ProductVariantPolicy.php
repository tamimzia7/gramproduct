<?php

namespace App\Policies;

use App\Models\ProductVariant;
use App\Models\User;

class ProductVariantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('products.variants.view');
    }

    public function view(User $user, ProductVariant $variant): bool
    {
        return $user->hasPermission('products.variants.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('products.variants.create');
    }

    public function update(User $user, ProductVariant $variant): bool
    {
        return $user->hasPermission('products.variants.edit');
    }

    public function delete(User $user, ProductVariant $variant): bool
    {
        return $user->hasPermission('products.variants.delete');
    }
}
