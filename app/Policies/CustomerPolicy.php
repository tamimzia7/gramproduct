<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function viewProfile(User $user, User $customer): bool
    {
        return $user->id === $customer->id;
    }

    public function updateProfile(User $user, User $customer): bool
    {
        return $user->id === $customer->id;
    }

    public function manageAddresses(User $user, User $customer): bool
    {
        return $user->id === $customer->id;
    }
}
