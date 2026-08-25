<?php

namespace App\Providers;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin.access', fn (User $user) => $user->isActive() && $user->hasAnyRole());

        foreach (RoleSeeder::PERMISSIONS as $permission) {
            Gate::define($permission, fn (User $user) => $user->isActive() && $user->hasPermission($permission));
        }

        // বাংলা সংখ্যা ফরম্যাটিং — @bn(120) → ১২০
        Blade::directive('bn', function ($expression) {
            return "<?php echo \\App\\Support\\BengaliNumber::format($expression); ?>";
        });

        // এককসহ মূল্য — @price(120, 'কেজি') → ৳১২০ / কেজি
        Blade::directive('price', function ($expression) {
            return "<?php echo \\App\\Support\\BengaliNumber::priceWithUnit($expression); ?>";
        });
    }
}
