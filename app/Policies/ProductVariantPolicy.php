<?php

namespace App\Policies;

use App\Models\ProductVariant;
use App\Models\User;

class ProductVariantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-products');
    }

    public function view(User $user, ProductVariant $variant): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-products');
    }

    public function update(User $user, ProductVariant $variant): bool
    {
        return $user->can('manage-products');
    }

    public function delete(User $user, ProductVariant $variant): bool
    {
        return $user->can('manage-products');
    }
}
