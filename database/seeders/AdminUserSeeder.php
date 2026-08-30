<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * .env-ভিত্তিক প্রাথমিক অ্যাডমিন অ্যাকাউন্ট তৈরি।
     *
     * পাসওয়ার্ড হার্ডকোড করা হয় না — শুধু লোকাল .env-এর ADMIN_PASSWORD থেকে
     * নেওয়া হয় এবং User মডেলের 'password -> hashed' কাস্ট হ্যাশ করে সংরক্ষণ করে।
     * ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD তিনটিই সেট না থাকলে কিছুই হয় না।
     * একই ইমেইলে আগে থেকে ইউজার থাকলে নতুন তৈরি হয় না — ডুপ্লিকেট নিষিদ্ধ।
     */
    public function run(): void
    {
        $name = config('shop.admin.name');
        $email = config('shop.admin.email');
        $password = config('shop.admin.password');

        if (! $name || ! $email || ! $password) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        if ($user->wasRecentlyCreated) {
            $user->forceFill([
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        }

        $user->assignRole(config('shop.admin.role', 'super-admin'));
    }
}
