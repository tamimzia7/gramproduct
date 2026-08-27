<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductService;
use App\Support\BengaliNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // টেস্টের জন্য super-admin রোল তৈরি
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

    /**
     * নির্দিষ্ট permission-যুক্ত ইউজার (super-admin নয়) — granular gate যাচাইয়ের জন্য
     */
    private function createUserWithPermissions(array $permissions): User
    {
        $role = Role::updateOrCreate(
            ['slug' => 'perm-'.md5(implode(',', $permissions))],
            ['name' => 'Perm Test', 'description' => '', 'permissions' => $permissions, 'is_system' => false]
        );

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * GD extension ছাড়াই বৈধ PNG ফাইল তৈরি (1x1 transparent pixel)
     */
    private function fakeImage(string $name = 'image.png'): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function validPayload(array $overrides = []): array
    {
        $category = Category::factory()->create();

        return array_merge([
            'name' => 'নাজিরশাইল চাল',
            'sku' => 'RICE-NS-001',
            'category_id' => $category->id,
            'short_description' => 'গ্রামের মিল থেকে সংগ্রহ করা উন্নত মানের নাজিরশাইল চাল।',
            'base_price' => 120,
            'compare_at_price' => 140,
            'unit' => 'kg',
            'stock_status' => 'in_stock',
            'is_active' => true,
            'is_featured' => true,
        ], $overrides);
    }

    // ===================== Authorization =====================

    public function test_guest_is_redirected_from_admin_products(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect();
    }

    public function test_plain_customer_cannot_view_admin_products(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_plain_customer_cannot_create_product(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $category = Category::factory()->create();

        $this->actingAs($user)->post(route('admin.products.store'), [
            'name' => 'পণ্য', 'category_id' => $category->id, 'base_price' => 10, 'stock_status' => 'in_stock',
        ])->assertForbidden();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_user_with_products_view_permission_can_access_index(): void
    {
        $user = $this->createUserWithPermissions(['view-dashboard', 'products.view']);

        $this->actingAs($user)->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_unauthorized_response_is_bengali_friendly(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('admin.products.create'))
            ->assertForbidden()
            ->assertSee('এই কাজটি করার অনুমতি আপনার নেই।');
    }

    // ===================== Admin CRUD =====================

    public function test_admin_can_view_products_index(): void
    {
        $admin = $this->createAdmin();
        Product::factory()->create(['name' => 'দেশি কৈ মাছ']);

        $this->actingAs($admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('পণ্যসমূহ')
            ->assertSee('দেশি কৈ মাছ');
    }

    public function test_admin_can_view_create_form(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('নতুন পণ্য যোগ করুন');
    }

    public function test_admin_can_store_product(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'নাজিরশাইল চাল',
            'sku' => 'RICE-NS-001',
            'is_featured' => true,
        ]);
    }

    public function test_admin_can_view_product_details(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['name' => 'তাজা লাউ']);

        $this->actingAs($admin)->get(route('admin.products.show', $product))
            ->assertOk()
            ->assertSee('তাজা লাউ');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('পণ্য সম্পাদনা করুন');
    }

    public function test_admin_can_update_product(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['name' => 'পুরনো নাম', 'slug' => 'purono-nam']);

        $payload = $this->validPayload([
            'name' => 'নতুন নাম',
            'sku' => $product->sku,
            'category_id' => $product->category_id,
        ]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), $payload)
            ->assertRedirect();

        $product->refresh();
        $this->assertEquals('নতুন নাম', $product->name);
    }

    public function test_update_keeps_slug_unchanged_when_not_provided(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['slug' => 'existing-url']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), $this->validPayload([
            'name' => 'সম্পূর্ণ নতুন নাম',
            'sku' => $product->sku,
            'category_id' => $product->category_id,
        ]))->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'slug' => 'existing-url',
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))
            ->assertRedirect();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    // ===================== Validation (Bengali) =====================

    public function test_name_required_returns_bengali_message(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['name' => '']));
        $response->assertSessionHasErrors('name');
        $this->assertStringContainsString('পণ্যের নাম লিখুন', session('errors')->first('name'));
    }

    public function test_category_required_returns_bengali_message(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['category_id' => null]))
            ->assertSessionHasErrors('category_id');
        $this->assertStringContainsString('ক্যাটাগরি নির্বাচন করুন', session('errors')->first('category_id'));
    }

    public function test_invalid_category_rejected_with_bengali_message(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['category_id' => 99999]))
            ->assertSessionHasErrors('category_id');
        $this->assertStringContainsString('নির্বাচিত ক্যাটাগরি সঠিক নয়', session('errors')->first('category_id'));
    }

    public function test_base_price_required_and_numeric_validation(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['base_price' => null]))
            ->assertSessionHasErrors('base_price');

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['base_price' => -5]))
            ->assertSessionHasErrors('base_price');

        $this->assertStringContainsString('মূল্য শূন্যের কম হতে পারবে না', session('errors')->first('base_price'));
    }

    public function test_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'UNIQ-SKU-1']);
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['sku' => 'UNIQ-SKU-1']))
            ->assertSessionHasErrors('sku');

        $this->assertStringContainsString('এই SKU ইতোমধ্যে ব্যবহৃত হয়েছে', session('errors')->first('sku'));
    }

    public function test_slug_must_be_unique(): void
    {
        Product::factory()->create(['slug' => 'taken-slug']);
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['slug' => 'taken-slug']))
            ->assertSessionHasErrors('slug');
    }

    public function test_invalid_unit_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload(['unit' => 'kilometer']))
            ->assertSessionHasErrors('unit');
    }

    // ===================== Slug generation =====================

    public function test_bengali_name_without_sku_generates_random_slug(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'দেশি মধু',
            'category_id' => $category->id,
            'base_price' => 500,
            'stock_status' => 'in_stock',
        ])->assertRedirect();

        $product = Product::where('name', 'দেশি মধু')->first();

        $this->assertNotNull($product);
        // বাংলা থেকে Str::slug ফাঁকা → random fallback, Latin ও unique
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $product->slug);
    }

    public function test_slug_generation_falls_back_to_sku_for_bengali_names(): void
    {
        // Laravel বাংলা নামকে Latin-এ transliterate করে
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload([
            'name' => 'নাজিরশাইল চাল',
            'sku' => 'RICE-NS-777',
            'slug' => null,
        ]))->assertRedirect();

        $product = Product::where('sku', 'RICE-NS-777')->first();

        $this->assertNotNull($product);
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $product->slug);
        $this->assertStringContainsString('najirsail', $product->slug);
    }

    public function test_sku_fallback_used_when_name_transliterates_to_empty(): void
    {
        $service = new ProductService;

        // প্রতীক-মাত্র নাম → Str::slug ফাঁকা → SKU fallback
        $this->assertEquals('rice-ns-777', $service->generateUniqueSlug('??? ???', null, 'RICE-NS-777'));

        // SKU-ও না থাকলে random Latin slug
        $random = $service->generateUniqueSlug('??? ???');
        $this->assertMatchesRegularExpression('/^product-[a-z0-9]{6}$/', $random);
    }

    public function test_duplicate_manual_slug_is_rejected_with_error(): void
    {
        Product::factory()->create(['slug' => 'dup-slug']);
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload([
            'slug' => 'dup-slug',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_auto_generated_slug_gets_unique_suffix_on_collision(): void
    {
        // প্রথম পণ্য: বাংলা নাম → najirsail-cal
        $this->actingAs($this->createAdmin())->post(route('admin.products.store'), $this->validPayload())
            ->assertRedirect();

        // Service সরাসরি — একই ভিত্তি slug-এ collision হলে suffix
        $service = new ProductService;
        $this->assertEquals('najirsail-cal-1', $service->generateUniqueSlug('নাজিরশাইল চাল'));
    }

    // ===================== Public listing =====================

    public function test_active_products_are_publicly_visible(): void
    {
        $product = Product::factory()->create([
            'name' => 'দেশি কৈ মাছ',
            'base_price' => 380,
            'unit' => 'kg',
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('দেশি কৈ মাছ')
            ->assertSee(BengaliNumber::money(380));
    }

    public function test_inactive_products_hidden_from_public_listing(): void
    {
        Product::factory()->inactive()->create(['name' => 'গোপন পণ্য']);
        Product::factory()->create(['name' => 'দৃশ্যমান পণ্য']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('দৃশ্যমান পণ্য')
            ->assertDontSee('গোপন পণ্য');
    }

    public function test_inactive_product_details_return_404(): void
    {
        $product = Product::factory()->inactive()->create(['slug' => 'hidden-one']);

        $this->get(route('products.show', $product))
            ->assertNotFound()
            ->assertSee('পেজটি খুঁজে পাওয়া যায়নি।');
    }

    public function test_out_of_stock_badge_displayed(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('স্টক শেষ');
    }

    public function test_in_stock_label_displayed(): void
    {
        $product = Product::factory()->create(['stock_status' => 'in_stock']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('স্টকে আছে');
    }

    public function test_category_filtering_works(): void
    {
        $fishCat = Category::factory()->create(['name' => 'মাছ', 'slug' => 'machh']);
        $vegCat = Category::factory()->create(['name' => 'সবজি', 'slug' => 'shobji']);

        Product::factory()->create(['name' => 'কৈ মাছ', 'category_id' => $fishCat->id]);
        Product::factory()->create(['name' => 'তাজা লাউ', 'category_id' => $vegCat->id]);

        $this->get(route('products.index', ['category' => 'machh']))
            ->assertOk()
            ->assertSee('কৈ মাছ')
            ->assertDontSee('তাজা লাউ');
    }

    public function test_search_filters_products(): void
    {
        Product::factory()->create(['name' => 'খাঁটি সুন্দরবনের মধু']);
        Product::factory()->create(['name' => 'নাজিরশাইল চাল']);

        $this->get(route('products.index', ['q' => 'মধু']))
            ->assertOk()
            ->assertSee('খাঁটি সুন্দরবনের মধু')
            ->assertDontSee('নাজিরশাইল চাল');
    }

    public function test_search_matches_sku_too(): void
    {
        Product::factory()->create(['name' => 'রহস্যময় নাম', 'sku' => 'HONEY-99']);
        Product::factory()->create(['name' => 'অন্য পণ্য', 'sku' => 'OTHER-11']);

        $this->get(route('products.index', ['q' => 'HONEY-99']))
            ->assertOk()
            ->assertSee('রহস্যময় নাম')
            ->assertDontSee('অন্য পণ্য');
    }

    public function test_sort_by_price_ascending(): void
    {
        $expensive = Product::factory()->create(['base_price' => 900, 'discount_price' => null]);
        $cheap = Product::factory()->create(['base_price' => 40, 'discount_price' => null]);

        $response = $this->get(route('products.index', ['sort' => 'price_asc']));
        $response->assertOk();

        $items = $response->viewData('products')->items();
        $this->assertSame($cheap->id, $items[0]->id);
        $this->assertNotSame($expensive->id, $items[0]->id);
    }

    public function test_pagination_works(): void
    {
        Product::factory()->count(13)->create();

        $response = $this->get(route('products.index'));
        $response->assertOk();
        $this->assertCount(12, $response->viewData('products'));

        $this->get(route('products.index', ['page' => 2]))
            ->assertOk();
    }

    public function test_empty_state_is_bengali(): void
    {
        $this->get(route('products.index', ['q' => 'অসম্ভব-অনুসন্ধান-xyz']))
            ->assertOk()
            ->assertSee('কোনো পণ্য পাওয়া যায়নি।');
    }

    // ===================== Product details page =====================

    public function test_details_page_shows_all_required_bengali_labels(): void
    {
        $product = Product::factory()->create([
            'name' => 'নাজিরশাইল চাল',
            'base_price' => 120,
            'unit' => 'kg',
            'sku' => 'RICE-NS-001',
            'description' => 'উন্নত মানের বর্ণনা।',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('নাজিরশাইল চাল')
            ->assertSee('মূল্য')
            ->assertSee('৳১২০ / কেজি')
            ->assertSee('কোড')
            ->assertSee('RICE-NS-001')
            ->assertSee('পণ্যের বিবরণ')
            ->assertSee('কার্টে যোগ করুন')
            ->assertSee('এখনই কিনুন');
    }

    public function test_old_price_with_discount_percent_displayed(): void
    {
        $product = Product::factory()->create([
            'base_price' => 120,
            'compare_at_price' => 150,
            'discount_price' => null,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee(BengaliNumber::money(150))
            ->assertSee(BengaliNumber::format(20).'%'); // ২০% ছাড়
    }

    public function test_dynamic_category_breadcrumb_rendered(): void
    {
        $root = Category::factory()->create(['name' => 'চাল ও ডাল']);
        $child = Category::factory()->create(['name' => 'কাটারিভোগ চাল', 'parent_id' => $root->id]);
        $product = Product::factory()->create(['name' => 'বিশেষ চাল', 'category_id' => $child->id]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        // হার্ডকোড নয় — ডায়নামিকভাবে ক্যাটাগরি চেইন আসছে
        $response->assertSee('হোম');
        $response->assertSee('চাল ও ডাল');
        $response->assertSee('কাটারিভোগ চাল');
        $response->assertSee('বিশেষ চাল');
    }

    public function test_seo_title_fallbacks(): void
    {
        $product = Product::factory()->create(['seo_title' => 'কাস্টম SEO শিরোনাম']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('<title>কাস্টম SEO শিরোনাম</title>', false);

        $fallback = Product::factory()->create(['seo_title' => null, 'name' => 'ফলব্যাক নাম']);

        $this->get(route('products.show', $fallback))
            ->assertOk()
            ->assertSee('<title>ফলব্যাক নাম</title>', false);
    }

    public function test_flag_badges_on_card(): void
    {
        Product::factory()->create([
            'is_new_arrival' => true,
            'is_featured' => true,
            'is_bestseller' => true,
            'is_seasonal' => true,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('নতুন')
            ->assertSee('বিশেষ')
            ->assertSee('সেরা বিক্রি')
            ->assertSee('মৌসুমি');
    }

    // ===================== Images =====================

    public function test_main_image_upload_on_store_creates_primary_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload([
            'image' => $this->fakeImage(),
            'image_alt_text' => 'নাজিরশাইল চালের প্যাকেট',
        ]))->assertRedirect();

        $product = Product::where('sku', 'RICE-NS-001')->with('primaryImage')->first();
        $image = $product->primaryImage;

        $this->assertNotNull($image);
        $this->assertTrue($image->is_primary);
        $this->assertEquals('নাজিরশাইল চালের প্যাকেট', $image->alt_text);
        $this->assertStringStartsWith('products/', $image->image_path);
        Storage::disk('public')->assertExists($image->image_path);
    }

    public function test_additional_images_upload(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload([
            'images' => [
                $this->fakeImage(),
                $this->fakeImage(),
            ],
        ]))->assertRedirect();

        $product = Product::where('sku', 'RICE-NS-001')->with('images')->first();

        // প্রথম অতিরিক্ত ছবিটিই primary হয়
        $this->assertCount(2, $product->images);
        $this->assertTrue($product->images->firstWhere('is_primary') !== null);
    }

    public function test_invalid_file_type_rejected_with_bengali_message(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload([
            'image' => UploadedFile::fake()->createWithContent('doc.pdf', 'not-an-image'),
        ]))->assertSessionHasErrors('image');

        $this->assertStringContainsString('অনুগ্রহ করে একটি বৈধ ছবি নির্বাচন করুন', session('errors')->first('image'));
    }

    public function test_make_primary_action(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $product = Product::factory()->has(ProductImage::factory()->count(2), 'images')->create();

        $second = $product->images()->orderByDesc('id')->first();
        $second->update(['is_primary' => true]);

        $response = $this->actingAs($admin)->patch(
            route('admin.products.images.primary', [$product, $product->images()->orderBy('id')->first()])
        );
        $response->assertRedirect();

        $product->refresh();
        $primaries = $product->images()->where('is_primary', true)->get();
        $this->assertCount(1, $primaries);
        $this->assertTrue($primaries->first()->is($product->images()->orderBy('id')->first()));
    }

    public function test_image_deletion_removes_file_and_promotes_replacement(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), $this->validPayload([
            'image' => $this->fakeImage(),
            'images' => [$this->fakeImage()],
        ]))->assertRedirect();

        $product = Product::where('sku', 'RICE-NS-001')->with('images')->first();
        $primary = $product->primaryImage;

        Storage::disk('public')->assertExists($primary->image_path);

        $this->actingAs($admin)->delete(route('admin.products.images.destroy', [$product, $primary]))
            ->assertRedirect();

        // ফাইল ও DB রেকর্ড দুটোই মুছে গেছে
        Storage::disk('public')->assertMissing($primary->image_path);
        $this->assertDatabaseMissing('product_images', ['id' => $primary->id]);

        // প্রথম বাকি ছবিটি primary হয়ে গেছে
        $replacement = $product->images()->orderBy('sort_order')->orderBy('id')->first();
        $this->assertNotNull($replacement);
        $this->assertTrue((bool) $replacement->is_primary);
    }

    // ===================== Model / helpers =====================

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create(['name' => 'মসলা']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($product->category->is($category));
    }

    public function test_unit_enum_labels_are_bengali(): void
    {
        $product = Product::factory()->make(['unit' => 'kg']);
        $this->assertEquals('কেজি', $product->unitLabel());

        $liter = Product::factory()->make(['unit' => 'liter']);
        $this->assertEquals('লিটার', $liter->unitLabel());
    }

    public function test_effective_price_matches_cart_logic(): void
    {
        $plain = Product::factory()->make(['base_price' => 100, 'discount_price' => null]);
        $discounted = Product::factory()->make(['base_price' => 100, 'discount_price' => 80]);

        $this->assertEquals('100.00', $plain->effectivePrice());
        $this->assertEquals('80.00', $discounted->effectivePrice());
    }

    public function test_bengali_number_formatting(): void
    {
        $this->assertEquals('১২০', BengaliNumber::format(120));
        $this->assertEquals('৪.৮', BengaliNumber::format('4.8'));
        $this->assertEquals('৳১২০', BengaliNumber::money(120));
        $this->assertEquals('৳১২০ / কেজি', BengaliNumber::priceWithUnit(120, 'কেজি'));
    }

    // ===================== Bengali UI audit =====================

    public function test_customer_pages_contain_no_unintended_english_ui_strings(): void
    {
        $product = Product::factory()->create([
            'name' => 'নাজিরশাইল চাল',
            'base_price' => 120,
            'unit' => 'kg',
            'sku' => 'RICE-NS-001',
        ]);

        $forbidden = [
            'Add to Cart', 'Buy Now', 'View Details', 'No products found',
            'Out of Stock', 'Add to Wishlist', 'Price:', 'Submit', 'Search...',
        ];

        foreach ([route('products.index'), route('products.show', $product)] as $url) {
            $response = $this->get($url);
            $response->assertOk();

            foreach ($forbidden as $needle) {
                $content = $response->getContent();
                $this->assertStringNotContainsString($needle, $content, "\"{$needle}\" found on {$url}");
            }
        }
    }
}
