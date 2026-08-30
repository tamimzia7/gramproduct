<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomepageTest extends TestCase
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

    private function createAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');

        return $user;
    }

    private function makeCategory(string $slug, array $overrides = []): Category
    {
        return Category::factory()->create(array_merge([
            'slug' => $slug,
            'is_active' => true,
        ], $overrides));
    }

    // ===================== 1-2. Homepage & categories =====================

    public function test_homepage_loads_with_hero(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('গ্রামের খাঁটি স্বাদ, আপনার ঘরে')
            ->assertSee('পণ্য দেখুন')
            ->assertSee('ক্যাটাগরি দেখুন');
    }

    public function test_homepage_displays_all_active_top_level_categories(): void
    {
        // §1 — কোনো সীমা নেই: ১২টি তৈরি করলে ১২টিই দেখাবে
        $names = ['চাল', 'মাছ', 'সবজি', 'বীজ', 'ডাল', 'মসলা', 'মধু', 'ফল', 'তেল', 'আচার', 'ঘি', 'অন্যান্য'];

        foreach ($names as $i => $name) {
            $this->makeCategory('cat-'.($i + 1), ['name' => $name]);
        }

        $content = $this->get(route('home'))->getContent();

        foreach ($names as $name) {
            $this->assertStringContainsString($name, $content);
        }

        // section header — নতুন শিরোনাম
        $this->assertStringContainsString('ক্যাটাগরি থেকে কিনুন', $content);
    }

    public function test_homepage_category_count_is_not_limited(): void
    {
        // ১৫টি — config limit (10) থাকলেও সবগুলো দেখাতে হবে
        foreach (range(1, 15) as $i) {
            $this->makeCategory('limit-cat-'.$i, ['name' => 'ক্যাটাগরি '.$i]);
        }

        $section = $this->categorySectionHtml();

        foreach (range(1, 15) as $i) {
            $this->assertStringContainsString('ক্যাটাগরি '.$i, $section);
        }
    }

    public function test_child_categories_do_not_appear_as_homepage_cards(): void
    {
        $root = $this->makeCategory('chal', ['name' => 'চাল']);
        $this->makeCategory('nazirshail', ['name' => 'নাজিরশাইল', 'parent_id' => $root->id]);
        $this->makeCategory('kataribhog', ['name' => 'কাটারিভোগ', 'parent_id' => $root->id]);

        Product::factory()->create(['name' => 'নাজিরশাইল চাল', 'category_id' => $root->id]);

        $section = $this->categorySectionHtml();

        $this->assertStringContainsString('চাল', $section);
        $this->assertStringNotContainsString('নাজিরশাইল', $section);
        $this->assertStringNotContainsString('কাটারিভোগ', $section);

        // parent link → existing category page where children are browsable
        $this->get(route('categories.show', $root))
            ->assertOk()
            ->assertSee('নাজিরশাইল')
            ->assertSee($this->getRouteProductLinkAssertion());
    }

    private function getRouteProductLinkAssertion(): string
    {
        return 'চাল';
    }

    public function test_newly_activated_category_appears_automatically(): void
    {
        // Admin deactivate → add product → reactivate: homepage auto-reflects
        $cat = $this->makeCategory('auto-cat', ['name' => 'স্বয়ংক্রিয় ক্যাটাগরি', 'is_active' => false]);

        $this->assertStringNotContainsString(
            'স্বয়ংক্রিয় ক্যাটাগরি',
            $this->categorySectionHtml(),
        );

        $cat->update(['is_active' => true]);

        $this->assertStringContainsString(
            'স্বয়ংক্রিয় ক্যাটাগরি',
            $this->categorySectionHtml(),
        );
    }

    /**
     * homepage category strip markup extract
     */
    private function categorySectionHtml(): string
    {
        $content = $this->get(route('home'))->getContent();

        preg_match('/<section class="category-section">(.*?)<\/section>/s', $content, $matches);

        return $matches[1] ?? '';
    }

    /**
     * rice showcase section markup extract
     */
    private function riceShowcaseSectionHtml(): string
    {
        $content = $this->get(route('home'))->getContent();

        $start = strpos($content, '<section class="rice-showcase');
        if ($start === false) {
            return '';
        }

        $end = strpos($content, '</section>', $start);
        if ($end === false) {
            return '';
        }

        return substr($content, $start, $end - $start + strlen('</section>'));
    }

    public function test_homepage_hides_inactive_categories(): void
    {
        $this->makeCategory('hidden-cat', ['name' => 'লুকানো ক্যাটাগরি', 'is_featured' => true, 'is_active' => false]);

        $content = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('লুকানো ক্যাটাগরি', $content);
    }

    // ===================== 3-5. Featured / inactive products =====================

    public function test_homepage_displays_featured_products(): void
    {
        $product = Product::factory()->create(['name' => 'বিশেষ সরিষার তেল', 'is_featured' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('আমাদের সেরা পণ্য')
            ->assertSee($product->name)
            ->assertSee('কার্টে যোগ করুন');
    }

    public function test_showcase_represents_rice_alongside_other_products(): void
    {
        // rice config-tree + featured non-rice — showcase-এ দুটোই থাকবে
        $rice = $this->makeCategory('rice-grains', ['name' => 'চাল']);
        $nonFeaturedRice = Product::factory()->create([
            'name' => 'সাধারণ চাল',
            'category_id' => $rice->id,
            'is_featured' => false,
        ]);
        $featuredOther = Product::factory()->create(['name' => 'খাঁটি মধু', 'is_featured' => true]);

        // featured slot (8) পূরণ করার মতো অন্য featured product
        Product::factory()->count(7)->create(['is_featured' => true]);

        $content = $this->get(route('home'))->getContent();

        $this->assertStringContainsString($featuredOther->name, $content);
        $this->assertStringContainsString('সাধারণ চাল', $content); // rice fill কাজ করেছে
    }

    public function test_homepage_hides_inactive_products(): void
    {
        Product::factory()->inactive()->create(['name' => 'অদৃশ্য পণ্য', 'is_featured' => true]);
        Product::factory()->create(['name' => 'দৃশ্যমান পণ্য', 'is_featured' => true]);

        $content = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('অদৃশ্য পণ্য', $content);
        $this->assertStringContainsString('দৃশ্যমান পণ্য', $content);
    }

    // ===================== 10-12. Dynamic collection sections =====================

    public function test_rice_section_pulls_products_from_configured_category_tree(): void
    {
        $rice = $this->makeCategory('rice-grains', ['name' => 'চাল ও ডাল']);
        $nazir = $this->makeCategory('kataribhog-rice', ['name' => 'নাজিরশাইল', 'parent_id' => $rice->id]);

        $product = Product::factory()->create(['name' => 'নাজিরশাইল চাল', 'category_id' => $nazir->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('আমাদের চাল')
            ->assertSee($product->name);
    }

    public function test_rice_showcase_renders_child_quick_links_and_view_all(): void
    {
        $rice = $this->makeCategory('rice-grains', ['name' => 'চাল ও ডাল']);
        $kataribhog = $this->makeCategory('kataribhog-rice', ['name' => 'কাটারিভোগ', 'parent_id' => $rice->id]);
        $brown = $this->makeCategory('brown-rice', ['name' => 'লাল চাল', 'parent_id' => $rice->id]);

        Product::factory()->create(['name' => 'কাটারিভোগ চাল', 'category_id' => $kataribhog->id]);
        Product::factory()->create(['name' => 'লাল চাল', 'category_id' => $brown->id]);

        $content = $this->get(route('home'))->assertOk()->getContent();

        // শিরোনাম + view-all link (root ক্যাটাগরি পেজে)
        $this->assertStringContainsString('আমাদের চাল', $content);
        $this->assertStringContainsString('সব চাল দেখুন', $content);
        $this->assertStringContainsString(route('categories.show', $rice), $content);

        // child quick-links — dynamic, DB-driven
        $this->assertStringContainsString('কাটারিভোগ', $content);
        $this->assertStringContainsString(route('categories.show', $kataribhog), $content);
        $this->assertStringContainsString('লাল চাল', $content);
        $this->assertStringContainsString(route('categories.show', $brown), $content);

        // both products present
        $this->assertStringContainsString('কাটারিভোগ চাল', $content);
        $this->assertStringContainsString('লাল চাল', $content);
    }

    public function test_rice_showcase_dynamic_product_count_is_calculated(): void
    {
        $rice = $this->makeCategory('rice-grains', ['name' => 'চাল ও ডাল']);
        $child = $this->makeCategory('kataribhog-rice', ['name' => 'কাটারিভোগ', 'parent_id' => $rice->id]);

        Product::factory()->count(3)->create(['name' => 'চাল পণ্য', 'category_id' => $child->id]);

        $content = $this->get(route('home'))->assertOk()->getContent();

        // সংখ্যা ডায়নামিক — fake নয়
        $this->assertStringContainsString('৩ ধরনের চাল', $content);
    }

    public function test_rice_showcase_inactive_child_category_products_not_shown(): void
    {
        $rice = $this->makeCategory('rice-grains', ['name' => 'চাল ও ডাল']);
        $activeChild = $this->makeCategory('kataribhog-rice', ['name' => 'কাটারিভোগ', 'parent_id' => $rice->id]);
        $inactiveChild = $this->makeCategory('brown-rice', ['name' => 'লুকানো চাল', 'parent_id' => $rice->id, 'is_active' => false]);

        Product::factory()->create(['name' => 'দৃশ্যমান চাল', 'category_id' => $activeChild->id]);
        Product::factory()->create(['name' => 'লুকানো পণ্য', 'category_id' => $inactiveChild->id]);

        $riceSection = $this->riceShowcaseSectionHtml();

        $this->assertStringContainsString('আমাদের চাল', $riceSection);
        $this->assertStringContainsString('দৃশ্যমান চাল', $riceSection);
        $this->assertStringNotContainsString('লুকানো পণ্য', $riceSection);

        // inactive child-এর quick link নেই
        $this->assertStringNotContainsString(route('categories.show', $inactiveChild), $riceSection);
    }

    public function test_rice_showcase_hidden_without_active_products(): void
    {
        // root exists but no active products → section hidden entirely
        $this->makeCategory('rice-grains', ['name' => 'চাল ও ডাল']);

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('আমাদের চাল', $content);
        $this->assertStringNotContainsString('সব চাল দেখুন', $content);
    }

    public function test_section_is_hidden_gracefully_without_matching_category(): void
    {
        // rice-grains ক্যাটাগরি নেই — সেকশনটি ভেঙে পড়ার বদলে অদৃশ্য হবে
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('আমাদের চাল');
    }

    public function test_fish_section_shows_only_active_products_of_fish_tree(): void
    {
        $fish = $this->makeCategory('fish-seafood', ['name' => 'মাছ']);
        $freshwater = $this->makeCategory('freshwater-fish', ['name' => 'মিঠে পানির মাছ', 'parent_id' => $fish->id]);

        Product::factory()->create(['name' => 'দেশি কৈ মাছ', 'category_id' => $freshwater->id]);
        Product::factory()->inactive()->create(['name' => 'গোপন ইলিশ', 'category_id' => $freshwater->id]);

        $content = $this->get(route('home'))->getContent();

        $this->assertStringContainsString('তাজা মাছ', $content);
        $this->assertStringContainsString('দেশি কৈ মাছ', $content);
        $this->assertStringNotContainsString('গোপন ইলিশ', $content);
    }

    /**
     * fish showcase section markup extract
     */
    private function fishShowcaseSectionHtml(): string
    {
        $content = $this->get(route('home'))->getContent();

        $start = strpos($content, '<section class="fish-showcase');
        if ($start === false) {
            return '';
        }

        $end = strpos($content, '</section>', $start);
        if ($end === false) {
            return '';
        }

        return substr($content, $start, $end - $start + strlen('</section>'));
    }

    public function test_fish_showcase_renders_child_quick_links_and_view_all(): void
    {
        $fish = $this->makeCategory('fish-seafood', ['name' => 'মাছ']);
        $freshwater = $this->makeCategory('freshwater-fish', ['name' => 'মিঠে পানির মাছ', 'parent_id' => $fish->id]);
        $dried = $this->makeCategory('dried-fish', ['name' => 'শুঁটকি মাছ', 'parent_id' => $fish->id]);

        Product::factory()->create(['name' => 'দেশি কৈ মাছ', 'category_id' => $freshwater->id]);
        Product::factory()->create(['name' => 'লোনা শুঁটকি মাছ', 'category_id' => $dried->id]);

        $content = $this->get(route('home'))->assertOk()->getContent();

        // শিরোনাম + view-all link (root ক্যাটাগরি পেজে)
        $this->assertStringContainsString('তাজা মাছ', $content);
        $this->assertStringContainsString('সব মাছ দেখুন', $content);
        $this->assertStringContainsString(route('categories.show', $fish), $content);

        // child quick-links — dynamic, DB-driven
        $this->assertStringContainsString('মিঠে পানির মাছ', $content);
        $this->assertStringContainsString(route('categories.show', $freshwater), $content);
        $this->assertStringContainsString('শুঁটকি মাছ', $content);
        $this->assertStringContainsString(route('categories.show', $dried), $content);

        // both products present
        $this->assertStringContainsString('দেশি কৈ মাছ', $content);
        $this->assertStringContainsString('লোনা শুঁটকি মাছ', $content);
    }

    public function test_fish_showcase_dynamic_product_count_is_calculated(): void
    {
        $fish = $this->makeCategory('fish-seafood', ['name' => 'মাছ']);
        $freshwater = $this->makeCategory('freshwater-fish', ['name' => 'মিঠে পানির মাছ', 'parent_id' => $fish->id]);

        Product::factory()->count(3)->create(['name' => 'মাছ পণ্য', 'category_id' => $freshwater->id]);

        $content = $this->get(route('home'))->assertOk()->getContent();

        // সংখ্যা ডায়নামিক — fake নয়
        $this->assertStringContainsString('৩ ধরনের মাছ', $content);
    }

    public function test_fish_showcase_inactive_child_category_products_not_shown(): void
    {
        $fish = $this->makeCategory('fish-seafood', ['name' => 'মাছ']);
        $activeChild = $this->makeCategory('freshwater-fish', ['name' => 'মিঠে পানির মাছ', 'parent_id' => $fish->id]);
        $inactiveChild = $this->makeCategory('dried-fish', ['name' => 'লুকানো মাছ', 'parent_id' => $fish->id, 'is_active' => false]);

        Product::factory()->create(['name' => 'দৃশ্যমান কৈ মাছ', 'category_id' => $activeChild->id]);
        Product::factory()->create(['name' => 'লুকানো শুঁটকি', 'category_id' => $inactiveChild->id]);

        $fishSection = $this->fishShowcaseSectionHtml();

        $this->assertStringContainsString('তাজা মাছ', $fishSection);
        $this->assertStringContainsString('দৃশ্যমান কৈ মাছ', $fishSection);
        $this->assertStringNotContainsString('লুকানো শুঁটকি', $fishSection);

        // inactive child-এর quick link নেই
        $this->assertStringNotContainsString(route('categories.show', $inactiveChild), $fishSection);
    }

    public function test_fish_showcase_hidden_without_active_products(): void
    {
        // root exists but no active products → section hidden entirely
        $this->makeCategory('fish-seafood', ['name' => 'মাছ']);

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('তাজা মাছ', $content);
        $this->assertStringNotContainsString('সব মাছ দেখুন', $content);
    }

    public function test_fish_section_is_hidden_gracefully_without_matching_category(): void
    {
        // fish-seafood ক্যাটাগরি নেই — সেকশনটি ভেঙে পড়ার বদলে অদৃশ্য হবে
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('তাজা মাছ');
    }

    // ===================== 6-8. Listing, search, filter =====================

    public function test_product_listing_works(): void
    {
        $product = Product::factory()->create(['name' => 'তালিকার পণ্য']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_search_matches_product_and_category_names(): void
    {
        $honey = Product::factory()->create(['name' => 'সুন্দরবনের মধু']);
        $riceCat = $this->makeCategory('chal', ['name' => 'চাল']);
        Product::factory()->create(['name' => 'কাটারিভোগ চাল', 'category_id' => $riceCat->id]);

        // পণ্যের নামে
        $this->get(route('products.index', ['q' => 'মধু']))
            ->assertOk()
            ->assertSee($honey->name);

        // ক্যাটাগরির নামে
        $this->get(route('products.index', ['q' => 'চাল']))
            ->assertOk()
            ->assertSee('কাটারিভোগ চাল');
    }

    public function test_category_filtering_on_listing_works(): void
    {
        $fishCat = $this->makeCategory('machh', ['name' => 'মাছ']);
        $vegCat = $this->makeCategory('shobji', ['name' => 'সবজি']);

        $fish = Product::factory()->create(['name' => 'কৈ মাছ', 'category_id' => $fishCat->id]);
        Product::factory()->create(['name' => 'তাজা লাউ', 'category_id' => $vegCat->id]);

        $response = $this->get(route('products.index', ['category' => 'machh']));
        $response->assertOk();
        $this->assertTrue(str_contains($response->getContent(), $fish->name));
        $this->assertFalse(str_contains($response->getContent(), 'তাজা লাউ'));
    }

    // ===================== 9-10. Category pages =====================

    public function test_category_page_works_with_breadcrumb_and_seo(): void
    {
        $root = $this->makeCategory('chal', [
            'name' => 'চাল',
            'description' => 'দেশি চালের সংগ্রহ।',
            'seo_title' => 'খাঁটি দেশি চাল',
        ]);
        Product::factory()->create(['name' => 'নাজিরশাইল চাল', 'category_id' => $root->id]);

        $this->get(route('categories.show', $root))
            ->assertOk()
            ->assertSee('<title>খাঁটি দেশি চাল</title>', false)
            ->assertSee('হোম')
            ->assertSee('দেশি চালের সংগ্রহ।')
            ->assertSee('নাজিরশাইল চাল');
    }

    public function test_subcategories_listed_and_their_products_included_in_parent(): void
    {
        $root = $this->makeCategory('chal', ['name' => 'চাল']);
        $child = $this->makeCategory('nazirshail', ['name' => 'নাজিরশাইল', 'parent_id' => $root->id]);

        Product::factory()->create(['name' => 'কাটারিভোগ চাল', 'category_id' => $root->id]);
        $childProduct = Product::factory()->create(['name' => 'নাজিরশাইল স্পেশাল', 'category_id' => $child->id]);

        $content = $this->get(route('categories.show', $root))
            ->assertOk()
            ->getContent();

        // "এই ক্যাটাগরির ধরন" — সাব-ক্যাটাগরি তালিকা
        $this->assertStringContainsString('এই ক্যাটাগরির ধরন', $content);
        $this->assertStringContainsString('নাজিরশাইল', $content);

        // বংশধর ক্যাটাগরির পণ্যও মূল ক্যাটাগরি পেজে দেখা যায়
        $this->assertStringContainsString($childProduct->name, $content);

        // সাব-ক্যাটাগরি পেজও কাজ করে
        $this->get(route('categories.show', $child))
            ->assertOk()
            ->assertSee('নাজিরশাইল স্পেশাল');
    }

    public function test_inactive_category_page_returns_404(): void
    {
        $hidden = $this->makeCategory('luka-anno', ['name' => 'লুকানো', 'is_active' => false]);

        $this->get(route('categories.show', $hidden))->assertNotFound();
    }

    public function test_all_categories_index_page_works(): void
    {
        $cat = $this->makeCategory('machh', ['name' => 'মাছ']);
        $this->makeCategory('luka', ['name' => 'লুকানো ক্যাটাগরি', 'is_active' => false]);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee($cat->name)
            ->assertDontSee('লুকানো ক্যাটাগরি');
    }

    // ===================== 11-14. Product detail =====================

    private function makeDetailProduct(): Product
    {
        $product = Product::factory()->create([
            'name' => 'নাজিরশাইল চাল',
            'description' => 'উন্নত মানের বর্ণনা।',
        ]);

        ProductVariant::factory()->default()->create([
            'product_id' => $product->id,
            'name' => '১ কেজি',
            'price' => 120,
        ]);

        return $product;
    }

    public function test_product_detail_works(): void
    {
        $product = $this->makeDetailProduct();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('ভ্যারিয়েন্ট নির্বাচন করুন')
            ->assertSee(BengaliNumber::money(120))
            ->assertSee($product->description);
    }

    public function test_variant_selection_data_is_embedded_for_detail_page(): void
    {
        $product = $this->makeDetailProduct();
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => '৫ কেজি',
            'price' => 570,
            'sort_order' => 2,
        ]);

        $content = $this->get(route('products.show', $product))->getContent();

        // JS payload — উভয় ভ্যারিয়েন্টের ডেটা আগেই এমবেড করা
        $this->assertStringContainsString('১ কেজি', $content);
        $this->assertStringContainsString('৫ কেজি', $content);
        $this->assertMatchesRegularExpression('/"purchasable":true/u', $content);
    }

    public function test_unavailable_variant_reports_out_of_stock_status(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        Inventory::create([
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ]);

        $content = $this->get(route('products.show', $product))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/"purchasable":false/u', $content);
        $this->assertMatchesRegularExpression('/add-to-cart-btn[^>]*disabled/u', $content);
    }

    public function test_related_products_exclude_current_product(): void
    {
        $category = $this->makeCategory('chal');
        $current = Product::factory()->create(['name' => 'নিজের পণ্য', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'সম্পর্কিত পণ্য এক', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'সম্পর্কিত পণ্য দুই', 'category_id' => $category->id]);

        $content = $this->get(route('products.show', $current))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('সম্পর্কিত পণ্য', $content);
        $this->assertStringContainsString('সম্পর্কিত পণ্য এক', $content);

        // নিজের পণ্য নিজের রিলেটেড সেকশনে থাকবে না
        preg_match('/সম্পর্কিত পণ্য.*$/s', $content, $matches);
        if (isset($matches[0])) {
            $this->assertStringNotContainsString('নিজের পণ্য', $matches[0]);
        }
    }

    // ===================== 15-16. Pagination & empty states =====================

    public function test_product_listing_pagination_works(): void
    {
        Product::factory()->count(13)->create();

        $response = $this->get(route('products.index'));
        $response->assertOk();
        $this->assertCount(12, $response->viewData('products'));

        $this->get(route('products.index', ['page' => 2]))->assertOk();
    }

    public function test_empty_search_and_empty_category_show_bengali_messages(): void
    {
        $this->get(route('products.index', ['q' => 'অসম্ভব-xyz']))
            ->assertOk()
            ->assertSee('আপনার খোঁজার সাথে মিলেছে এমন কোনো পণ্য পাওয়া যায়নি।');

        $emptyCategory = $this->makeCategory('khali', ['name' => 'খালি ক্যাটাগরি']);

        $this->get(route('categories.show', $emptyCategory))
            ->assertOk()
            ->assertSee('এই ক্যাটাগরিতে এখনো কোনো পণ্য যোগ করা হয়নি।');
    }

    // ===================== 17-18. Bengali-only UI =====================

    public function test_customer_pages_use_bengali_labels_and_numbers(): void
    {
        $this->makeCategory('rice-grains', ['name' => 'চাল', 'is_featured' => true]);
        Product::factory()->create(['name' => 'নাজিরশাইল চাল', 'base_price' => 120]);

        foreach ([route('home'), route('products.index'), route('categories.index')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('হোম');
        }

        $this->get(route('products.index'))->assertSee('৳'.BengaliNumber::format(120));
    }

    public function test_customer_pages_contain_no_unintended_english_ui_strings(): void
    {
        $category = $this->makeCategory('machh', ['name' => 'মাছ']);
        $product = Product::factory()->create(['name' => 'কৈ মাছ', 'category_id' => $category->id]);

        $forbidden = [
            'Add to Cart', 'Buy Now', 'View Details', 'Out of Stock', 'In Stock',
            'Categories', 'Search...', 'Related Products', 'No products found', 'Submit',
        ];

        $urls = [
            route('home'),
            route('products.index'),
            route('categories.index'),
            route('categories.show', $category),
            route('products.show', $product),
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $response->assertOk();

            foreach ($forbidden as $needle) {
                $this->assertStringNotContainsString($needle, $response->getContent(), "\"{$needle}\" found on {$url}");
            }
        }
    }

    // ===================== 18. Trust / Why Choose Us =====================

    public function test_trust_section_renders_heading_and_four_feature_cards(): void
    {
        $content = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('কেন আমাদের কাছ থেকে কিনবেন?', $content);
        $this->assertStringContainsString(
            'গ্রামের পণ্য বেছে নেওয়া থেকে আপনার ঘরে পৌঁছানো পর্যন্ত আমরা গুরুত্ব দিই মান ও সহজ সেবায়।',
            $content,
        );

        foreach (['মানসম্মত পণ্য', 'গ্রামের উৎস', 'সহজ অর্ডার', 'নিরাপদ প্যাকেজিং'] as $title) {
            $this->assertStringContainsString($title, $content);
        }

        foreach ([
            'বাছাই করা পণ্যের প্রতি গুরুত্ব',
            'গ্রাম ও স্থানীয় উৎসের পণ্য এক জায়গায়',
            'সহজেই পণ্য বেছে নিয়ে অর্ডার করুন',
            'পণ্য নিরাপদে পৌঁছে দেওয়ার ব্যবস্থা',
        ] as $description) {
            $this->assertStringContainsString($description, $content);
        }
    }

    public function test_trust_cards_use_decorative_icons_with_visible_text(): void
    {
        $section = $this->trustSectionHtml();

        // icon টাই নিজে শুধু icon নয় — বাংলা title + description সবসময় আছে
        $this->assertGreaterThanOrEqual(4, substr_count($section, 'aria-hidden="true"'));

        foreach (['bi bi-patch-check', 'bi bi-tree', 'bi bi-bag-check', 'bi bi-box-seam'] as $icon) {
            $this->assertStringContainsString($icon, $section);
        }
    }

    public function test_trust_section_makes_no_unsupported_claims(): void
    {
        $content = $this->get(route('home'))->getContent();

        foreach (['১০০% অর্গানিক', '১০০% কেমিক্যাল ফ্রি', 'সরাসরি কৃষকের কাছ থেকে', '২৪ ঘণ্টায় ডেলিভারি'] as $claim) {
            $this->assertStringNotContainsString($claim, $content);
        }
    }

    public function test_trust_section_is_last_section_after_removed_promo_and_cta(): void
    {
        $content = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsString('মৌসুমি ও তাজা পণ্যের নিয়মিত সংগ্রহ', $content);
        $this->assertStringNotContainsString('গ্রামের আসল স্বাদ ঘরে আনুন', $content);
        $this->assertStringNotContainsString('গ্রামের উৎস থেকে সংগ্রহ', $content);

        // trust section পণ্য সেকশনগুলোর পরে, ফুটারের আগে শেষ সেকশন
        $trust = strpos($content, 'কেন আমাদের কাছ থেকে কিনবেন?');
        $featured = strpos($content, 'ক্যাটাগরি থেকে কিনুন');
        $this->assertNotFalse($trust, 'Trust heading not found.');
        $this->assertNotFalse($featured, 'Category section not found.');
        $this->assertGreaterThan($featured, $trust);
    }

    /**
     * trust section markup extract
     */
    private function trustSectionHtml(): string
    {
        $content = $this->get(route('home'))->getContent();

        $start = strpos($content, '<section class="trust-section');
        if ($start === false) {
            return '';
        }

        $end = strpos($content, '</section>', $start);
        if ($end === false) {
            return '';
        }

        return substr($content, $start, $end - $start + strlen('</section>'));
    }

    // ===================== Story / Village Origin =====================

    public function test_our_story_section_renders_heading_and_story_content(): void
    {
        $content = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('গ্রাম থেকে আপনার ঘরে', $content);
        $this->assertStringContainsString(
            'গ্রামের মাটি, ক্ষেত, বিল-ঝিল ও স্থানীয় উৎসের নানা পণ্যকে সহজভাবে আপনার ঘরে পৌঁছে দেওয়াই আমাদের লক্ষ্য।',
            $content,
        );
        $this->assertStringContainsString(
            'আমাদের যাত্রা গ্রামের পণ্যকে আরও সহজে মানুষের কাছে পৌঁছে দেওয়ার লক্ষ্যে।',
            $content,
        );
    }

    public function test_our_story_ctas_link_to_existing_routes(): void
    {
        $section = $this->ourStorySectionHtml();

        $this->assertStringContainsString('আমাদের সম্পর্কে জানুন', $section);
        $this->assertStringContainsString('পণ্য দেখুন', $section);
        $this->assertStringContainsString(route('categories.index'), $section);
        $this->assertStringContainsString(route('products.index'), $section);
    }

    public function test_our_story_visual_is_inline_svg_with_bengali_label(): void
    {
        $section = $this->ourStorySectionHtml();

        $this->assertStringContainsString('role="img"', $section);
        $this->assertStringContainsString('aria-label="গ্রামের ধানক্ষেত, বিল-ঝিল ও সবুজ মাঠের দৃশ্য"', $section);
        $this->assertStringContainsString('<svg', $section);
        $this->assertStringContainsString('aria-hidden="true"', $section);
    }

    public function test_our_story_makes_no_unsupported_claims(): void
    {
        $content = $this->get(route('home'))->getContent();

        foreach (['১০০% অর্গানিক', 'কেমিক্যাল মুক্ত', 'বিষমুক্ত', 'সরাসরি কৃষকের কাছ থেকে', 'নিজস্ব খামার', 'নিজস্ব মিল'] as $claim) {
            $this->assertStringNotContainsString($claim, $content);
        }
    }

    public function test_our_story_appears_after_trust_section(): void
    {
        $content = $this->get(route('home'))->getContent();

        $story = strpos($content, 'গ্রাম থেকে আপনার ঘরে');
        $trust = strpos($content, 'কেন আমাদের কাছ থেকে কিনবেন?');
        $this->assertNotFalse($story, 'Story heading not found.');
        $this->assertNotFalse($trust, 'Trust heading not found.');
        $this->assertGreaterThan($trust, $story);
    }

    /**
     * our story section markup extract
     */
    private function ourStorySectionHtml(): string
    {
        $content = $this->get(route('home'))->getContent();

        $start = strpos($content, '<section class="our-story');
        if ($start === false) {
            return '';
        }

        $end = strpos($content, '</section>', $start);
        if ($end === false) {
            return '';
        }

        return substr($content, $start, $end - $start + strlen('</section>'));
    }

    // ===================== 19. Admin content protection =====================

    public function test_admin_area_stays_protected_while_customer_pages_are_public(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect();
        $this->get(route('admin.inventory.index'))->assertRedirect();

        $this->get(route('home'))->assertOk();
    }

    // ===================== 20. N+1 guard =====================

    public function test_homepage_query_count_is_bounded(): void
    {
        // অনেক পণ্য থাকলেও কুয়েরি সংখ্যা সীমিত থাকতে হবে (eager loading)
        $rice = $this->makeCategory('rice-grains');
        Product::factory()->count(15)->create(['category_id' => $rice->id, 'is_featured' => true]);
        Product::factory()->count(15)->create(['category_id' => $rice->id]);

        DB::enableQueryLog();
        $this->get(route('home'))->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // ৩০টি পণ্য + ৪টি সেকশন থাকা সত্ত্বেও কুয়েরি < 60
        $this->assertLessThan(60, $queryCount, "Homepage executed {$queryCount} queries.");
    }
}
