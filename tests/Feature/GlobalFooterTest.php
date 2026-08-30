<?php

use App\Models\Category;
use App\Models\Role;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GlobalFooterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full access', 'permissions' => [], 'is_system' => true]
        );
    }

    // ===================== Site Footer =====================

    public function test_site_footer_renders_brand_columns_and_dynamic_copyright(): void
    {
        Cache::forget('footer.categories');

        $footer = $this->footerHtml();

        $this->assertStringContainsString('<footer class="site-footer', $footer);

        $this->assertStringContainsString(config('app.name'), $footer);
        $this->assertStringContainsString(asset('images/logo.png'), $footer);
        $this->assertStringContainsString(__('footer.brand_description'), $footer);

        foreach (['দ্রুত লিংক', 'পণ্যসমূহ', 'যোগাযোগ'] as $heading) {
            $this->assertStringContainsString($heading, $footer, "Missing footer heading: {$heading}");
        }

        $this->assertStringContainsString('© '.BengaliNumber::format(now()->year), $footer);
        $this->assertStringContainsString('সর্বস্বত্ব সংরক্ষিত।', $footer);
    }

    public function test_site_footer_quick_links_use_existing_routes(): void
    {
        $footer = $this->footerHtml();

        $this->assertStringContainsString('হোম', $footer);
        $this->assertStringContainsString('পণ্যসমূহ', $footer);
        $this->assertStringContainsString('href="'.route('home').'"', $footer);
        $this->assertStringContainsString('href="'.route('products.index').'"', $footer);
        $this->assertStringContainsString('href="'.route('categories.index').'"', $footer);
    }

    public function test_site_footer_categories_are_dynamic_active_ordered_and_limited(): void
    {
        Cache::forget('footer.categories');

        foreach (range(1, 8) as $i) {
            Category::factory()->create(['name' => 'ক্যাট '.$i, 'sort_order' => $i, 'is_active' => true]);
        }
        Category::factory()->create(['name' => 'লুকানো ক্যাট', 'sort_order' => 99, 'is_active' => false]);

        $footer = $this->footerHtml();
        $limit = (int) config('shop.footer.categories_limit');

        $this->assertStringContainsString('ক্যাট 1', $footer);
        $this->assertStringContainsString('ক্যাট '.$limit, $footer);
        $this->assertStringNotContainsString('ক্যাট '.($limit + 1), $footer);
        $this->assertStringNotContainsString('লুকানো ক্যাট', $footer);

        $this->assertStringContainsString('সব ক্যাটাগরি দেখুন', $footer);
        $this->assertStringContainsString('href="'.route('categories.index').'"', $footer);
    }

    public function test_site_footer_contact_shows_only_real_configured_data(): void
    {
        config([
            'shop.contact.phone' => '+8801712345678',
            'shop.contact.whatsapp' => null,
            'shop.contact.whatsapp_url' => null,
            'shop.contact.email' => 'support@gramproduct.test',
            'shop.contact.address' => 'গ্রামের বাজার, সদর, ঢাকা',
        ]);

        $footer = $this->footerHtml();

        $this->assertStringContainsString('href="tel:+8801712345678"', $footer);
        $this->assertStringContainsString('ফোন করে যোগাযোগ করুন', $footer);
        $this->assertStringContainsString(BengaliNumber::format('+8801712345678'), $footer);
        $this->assertStringContainsString('href="mailto:support@gramproduct.test"', $footer);
        $this->assertStringContainsString('গ্রামের বাজার, সদর, ঢাকা', $footer);

        $this->assertStringNotContainsString('wa.me', $footer);
        $this->assertStringNotContainsString('contact@example.com', $footer);
        $this->assertStringContainsString('+৮৮০১৭১২৩৪৫৬৭৮', $footer);
    }

    public function test_site_footer_whatsapp_uses_configured_number(): void
    {
        config([
            'shop.contact.phone' => null,
            'shop.contact.whatsapp' => '+880 1700-000000',
            'shop.contact.whatsapp_url' => null,
            'shop.contact.email' => null,
            'shop.contact.address' => null,
        ]);

        $footer = $this->footerHtml();

        $this->assertStringContainsString('href="https://wa.me/8801700000000"', $footer);
        $this->assertStringContainsString('WhatsApp-এ বার্তা পাঠান', $footer);
        $this->assertStringContainsString('target="_blank"', $footer);
    }

    public function test_site_footer_social_icons_only_when_configured(): void
    {
        config([
            'shop.social.facebook' => 'https://facebook.com/gramproduct',
            'shop.social.instagram' => null,
            'shop.social.youtube' => null,
            'shop.social.tiktok' => null,
        ]);

        $footer = $this->footerHtml();

        $this->assertStringContainsString('href="https://facebook.com/gramproduct"', $footer);
        $this->assertStringContainsString('ফেসবুক পেজ', $footer);
        $this->assertStringNotContainsString('instagram', $footer);
        $this->assertStringNotContainsString('youtube', $footer);
    }

    public function test_site_footer_hides_empty_contact_and_social_placeholders(): void
    {
        config([
            'shop.contact.phone' => null,
            'shop.contact.whatsapp' => null,
            'shop.contact.whatsapp_url' => null,
            'shop.contact.email' => null,
            'shop.contact.address' => null,
            'shop.social.facebook' => null,
            'shop.social.instagram' => null,
            'shop.social.youtube' => null,
            'shop.social.tiktok' => null,
        ]);

        $footer = $this->footerHtml();

        $this->assertStringNotContainsString('contact@example.com', $footer);
        $this->assertStringNotContainsString('মেইল:', $footer);
        $this->assertStringNotContainsString('site-footer-social', $footer);
    }

    public function test_site_footer_is_bengali_only(): void
    {
        $footer = $this->footerHtml();

        foreach (['Quick Links', 'Products', 'Contact', 'About Us', 'Privacy Policy', 'Terms & Conditions', 'Navigation', 'Quick Link'] as $english) {
            $this->assertStringNotContainsString($english, $footer, "English UI text leaked: {$english}");
        }
    }

    public function test_site_footer_responsive_grid_is_four_two_one(): void
    {
        $footer = $this->footerHtml();

        $this->assertStringContainsString('col-12 col-md-6 col-lg-4', $footer);
        $this->assertStringContainsString('col-6 col-md-3 col-lg-2', $footer);
        $this->assertStringContainsString('col-6 col-md-3 col-lg-3', $footer);
        $this->assertStringContainsString('col-12 col-lg-3', $footer);
    }

    public function test_site_footer_renders_on_non_home_pages_through_layout(): void
    {
        $productPage = $this->get(route('products.index'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('<footer class="site-footer', $productPage);

        $categoryPage = $this->get(route('categories.index'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('<footer class="site-footer', $categoryPage);
    }

    /**
     * site footer markup extract
     */
    private function footerHtml(): string
    {
        $content = $this->get(route('home'))->getContent();

        $start = strpos($content, '<footer class="site-footer');
        if ($start === false) {
            return '';
        }

        $end = strpos($content, '</footer>', $start);
        if ($end === false) {
            return '';
        }

        return substr($content, $start, $end - $start + strlen('</footer>'));
    }
}
