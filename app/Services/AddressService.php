<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**
     * নতুন ঠিকানা — প্রথম ঠিকানা হলে স্বয়ংক্রিয়ভাবে default
     */
    public function create(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data): Address {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            $hasAny = $user->addresses()->exists();

            if ($makeDefault || ! $hasAny) {
                $user->addresses()->update(['is_default' => false]);
                $data['is_default'] = true;
            } else {
                $data['is_default'] = false;
            }

            return $user->addresses()->create($data);
        });
    }

    /**
     * ঠিকানা হালনাগাদ — শুধু মালিকানাধীন ঠিকানা
     */
    public function update(User $user, Address $address, array $data): Address
    {
        abort_unless($address->user_id === $user->id, 403, __('checkout.errors.not_yours'));

        return DB::transaction(function () use ($user, $address, $data): Address {
            if ((bool) ($data['is_default'] ?? false)) {
                $user->addresses()
                    ->whereKeyNot($address->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                $data['is_default'] = true;
            } else {
                unset($data['is_default']);
            }

            $address->fill($data)->save();

            return $address->refresh();
        });
    }

    public function delete(User $user, Address $address): void
    {
        abort_unless($address->user_id === $user->id, 403, __('checkout.errors.not_yours'));

        DB::transaction(function () use ($user, $address): void {
            $wasDefault = $address->is_default;

            $address->delete();

            // ডিফল্ট মুছে গেলে সর্বশেষ ঠিকানা default হয় — inconsistent state থাকে না
            if ($wasDefault) {
                $replacement = $user->addresses()->latest()->first();

                if ($replacement) {
                    $replacement->forceFill(['is_default' => true])->save();
                }
            }
        });
    }

    /**
     * ডিফল্ট নির্ধারণ — আগের default বাদ (transaction)
     */
    public function setDefault(User $user, Address $address): void
    {
        abort_unless($address->user_id === $user->id, 403, __('checkout.errors.not_yours'));

        DB::transaction(function () use ($user, $address): void {
            $user->addresses()
                ->whereKeyNot($address->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $address->forceFill(['is_default' => true])->save();
        });
    }
}
