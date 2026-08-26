<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
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

        // বাংলা সংখ্যা ফরম্যাটিং — @bn(120) → ১২০
        Blade::directive('bn', function ($expression) {
            return "<?php echo \\App\\Support\\BengaliNumber::format($expression); ?>";
        });

        // এককসহ মূল্য — @price(120, 'কেজি') → ৳১২০ / কেজি
        Blade::directive('price', function ($expression) {
            return "<?php echo \\App\\Support\\BengaliNumber::priceWithUnit($expression); ?>";
        });

        // ফুটারের ডায়নামিক ক্যাটাগরি — component নিজে কুয়েরি চালায় না।
        // Eloquent মডেল ক্যাশ নিষিদ্ধ — শুধু প্লেইন array (সিরিয়ালাইজ-নিরাপদ)
        View::composer('components.footer', function ($view): void {
            $view->with('footerCategories', collect(
                Cache::remember('footer.categories', now()->addMinutes(30), fn (): array => Category::query()
                    ->active()
                    ->ordered()
                    ->take(config('shop.footer.categories_limit'))
                    ->get(['id', 'name', 'slug'])
                    ->map(fn (Category $category): array => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->all()),
            ));
        });

        // হেডারের লাইভ কার্ট-কাউন্ট — guest(session)/auth(user) উভয়ের জন্য
        View::composer(['components.navbar'], function ($view): void {
            $view->with('cartCount', app(CartService::class)
                ->getItemCount(auth()->user(), session()->getId()));
        });
    }
}
