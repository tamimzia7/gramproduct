<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use App\Services\CartService;
use App\Services\HomepageService;
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

        // গ্লোবাল ফুটার — ক্যাটাগরি নিজে কুয়েরি চালায় না; composer-থেকে শেয়ারড ডেটা।
        // Eloquent মডেল ক্যাশ নিষিদ্ধ — শুধু প্লেইন array (সিরিয়ালাইজ-নিরাপদ)।
        View::composer('components.site-footer', function ($view): void {
            $limit = (int) config('shop.footer.categories_limit');

            $categories = Cache::remember('footer.categories', now()->addMinutes(30), fn (): array => Category::query()
                ->active()
                ->ordered()
                ->take($limit + 1)
                ->get(['id', 'name', 'slug'])
                ->map(fn (Category $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])
                ->all());

            $view->with([
                'footerCategories' => collect(array_slice($categories, 0, $limit)),
                'footerHasMoreCategories' => count($categories) > $limit,
                'footerQuickLinks' => [
                    ['label' => __('footer.nav_home'), 'href' => route('home')],
                    ['label' => __('footer.nav_products'), 'href' => route('products.index')],
                    ['label' => __('footer.nav_categories'), 'href' => route('categories.index')],
                ],
                'footerContactActions' => app(HomepageService::class)->contactActions(),
                'footerAddress' => config('shop.contact.address'),
                'footerSocialItems' => $this->socialItems(),
            ]);
        });

        // হেডারের লাইভ কার্ট-কাউন্ট — guest(session)/auth(user) উভয়ের জন্য
        View::composer(['components.navbar'], function ($view): void {
            $view->with('cartCount', app(CartService::class)
                ->getItemCount(auth()->user(), session()->getId()));
        });
    }

    /**
     * শুধুমাত্র প্রকৃত কনফিগার করা সোশ্যাল মাধ্যম ফুটারের জন্য।
     * খালি/জাল URL কখনোই দেখানো হয় না।
     */
    private function socialItems(): array
    {
        $icons = [
            'facebook' => 'bi-facebook',
            'instagram' => 'bi-instagram',
            'youtube' => 'bi-youtube',
            'tiktok' => 'bi-tiktok',
        ];

        $items = [];

        foreach (config('shop.social') as $key => $url) {
            $url = (string) $url;

            if ($url === '' || ! isset($icons[$key])) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'href' => $url,
                'icon' => $icons[$key],
                'aria' => __("footer.social_aria_{$key}"),
                'external' => true,
            ];
        }

        return $items;
    }
}
