---
paths:
  - database/seeders/AdminUserSeeder.php
---

# Seeders

## Admin bootstrap: env-driven seeder + shared login redirect
The initial admin account comes from config('shop.admin') which is .env-driven (ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD). NEVER hard-code admin credentials (email is a config value too — keep the real one only in the local .env, use synthetic values in tests). The seeder no-ops unless all three values are set; it firstOrCreates by email (no duplicates), only sets is_active/email_verified_at/password on creation (won't overwrite an admin-changed password on re-seed), and always assignRole(config('shop.admin.role')) which defaults to the existing system role 'super-admin' (never create a new role system). Password is hashed by the User model's 'password => hashed' cast. Role-holders log in through the SAME /login route as customers (no separate /admin/login); LoginController redirects them to route('admin.dashboard') via $user->hasAnyRole(). Non-role users still go to home.
