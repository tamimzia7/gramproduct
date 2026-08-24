<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        View::composer('components.navbar', function ($view) {
            $view->with('navCategories', Category::active()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->active()->ordered()])
                ->ordered()
                ->get());
        });
    }
}
