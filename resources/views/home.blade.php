<x-layouts.app
    :title="__('home.meta.title')"
    :meta-description="__('home.meta.description')">

    @push('head')
        {{-- Hero-র প্রধান ছবি above-the-fold — lazy load নয়, eager preload --}}
        <link rel="preload" as="image" href="{{ asset('images/b-image1.jpg') }}" fetchpriority="high">
    @endpush

    {{-- ============================== Hero ==============================
         তিনটি বিদ্যমান গ্রামীণ ছবির blended background composition:
         b-image1 (কৃষক/ধানক্ষেত — বামে) · b-image2 (বিল/মাছ — ডানে)
         b-image3 (ধানক্ষেত landscape — ভিত্তি/গভীরতা) --}}
    <section class="hero-scene">
        <div class="hero-bg" aria-hidden="true">
            <div class="hero-bg__base"></div>
            <div class="hero-bg__left"></div>
            <div class="hero-bg__right"></div>
        </div>

        {{-- প্যানেল — hero-র গাণিতিক কেন্দ্রে (flex center) --}}
        <div class="hero-text-panel text-center">
            <h1 class="hero-heading fw-bold text-success-emphasis mb-3">
                {{ __('home.hero.title') }}
            </h1>
            <p class="lead mb-4">
                {{ __('home.hero.subtitle') }}
            </p>
            <div class="hero-actions">
                <a href="{{ route('products.index') }}" class="btn btn-success btn-lg px-4">
                    <i class="bi bi-basket2 me-2"></i>{{ __('home.hero.cta_primary') }}
                </a>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-success btn-lg px-4 bg-white bg-opacity-75">
                    {{ __('home.hero.cta_secondary') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ======================= Category Section =======================
         সব active top-level ক্যাটাগরি — কোনো সীমা নেই;
         child-রা ক্যাটাগরি পেজে browse হয়। Grid rows auto-grow. --}}
    <section class="category-section">
        <div class="container">
            <x-section-header
                    :title="__('home.quick_categories.title')"
                    :subtitle="__('home.quick_categories.subtitle')"
                    :view-all-url="route('categories.index')"
                    :view-all-text="__('home.quick_categories.view_all')" />

            @if ($quickCategories->isEmpty())
                <p class="text-muted text-center py-3 mb-0">
                    {{ __('home.empty.no_categories') }}
                </p>
            @else
                <div class="category-strip" role="list">
                    @foreach ($quickCategories as $category)
                        <div role="listitem">
                            <x-category-card :category="$category" />
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4 d-lg-none">
                    <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        {{ __('home.quick_categories.view_all') }}
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ======================= Product Showcase =======================
         Categories-এর ঠিক নিচে — featured → rice fill → latest (config-driven) --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="py-5 pt-4">
            <div class="container">
                <x-section-header
                        :title="__('home.featured.title')"
                        :subtitle="__('home.featured.subtitle')"
                        :view-all-url="route('products.index')"
                        :view-all-text="__('home.featured.view_all')" />
                <x-product-grid :products="$featuredProducts" />
            </div>
        </section>
    @endif

    {{-- ======================= Rice Showcase =======================
         চাল — বিশেষ হোমপেজ ফোকাস; সব ডেটা HomepageService থেকে
         (component নিজে কোনো কুয়েরি চালায় না)। ক্যাটাগরি/কনফিগ না থাকলে বাদ। --}}
    @if (! empty($riceShowcase))
        <x-rice-showcase
                :products="$riceShowcase['products']"
                :categories="$riceShowcase['children']"
                :root-category="$riceShowcase['rootCategory']"
                :product-count="$riceShowcase['productCount']" />
    @endif

    {{-- ======================= Fresh Fish Showcase =======================
         তাজা মাছ — ডেডিকেটেড হোমপেজ ফোকাস; সব ডেটা HomepageService থেকে
         (component নিজে কোনো কুয়েরি চালায় না)। ক্যাটাগরি/কনফিগ না থাকলে বাদ। --}}
    @if (! empty($fishShowcase))
        <x-fish-showcase
                :products="$fishShowcase['products']"
                :categories="$fishShowcase['children']"
                :root-category="$fishShowcase['rootCategory']"
                :product-count="$fishShowcase['productCount']" />
    @endif

    {{-- ==================== Dynamic collection sections ==================== --}}
    @foreach ($sections as $section)
        <section class="py-5 {{ $loop->even ? 'bg-light' : '' }}">
            <div class="container">
                <x-section-header
                        :title="$section['title']"
                        :subtitle="$section['subtitle']"
                        :view-all-url="route('categories.show', $section['category'])"
                        :view-all-text="__('home.sections.view_all')" />
                <x-product-grid :products="$section['products']" />
            </div>
        </section>
    @endforeach

    {{-- ==================== Trust / Why Choose Us ====================
         "কেন এখান থেকে কিনব?" — ফুটারের আগে বিশ্বাস গঠনের সেকশন।
         স্ট্যাটিক কনটেন্ট lang জনিত; সব ডেটা props-এর মাধ্যমে
         (component নিজে কোনো কুয়েরি চালায় না)। --}}
    <section class="trust-section py-5 bg-white">
        <div class="container">
            <x-section-header :title="__('home.why.title')" :subtitle="__('home.why.subtitle')" />
            <div class="row g-3 g-md-4 row-cols-1 row-cols-sm-2 row-cols-xl-4">
                @foreach (__('home.why.items') as $item)
                    <div class="col d-flex">
                        <x-trust-feature-card
                                :icon="$item['icon']"
                                :title="$item['title']"
                                :description="$item['description']" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== Our Story / Village Origin ====================
         "গ্রাম থেকে আপনার ঘরে" — বিশ্বাস-পর্বের পর গ্রামীণ উৎসের গল্প।
         স্ট্যাটিক কনটেন্ট lang জনিত; কোনো DB কুয়েরি নেই।
         ভিজ্যুয়াল: hand-crafted inline SVG দৃশ্য (ডাউনলোড/জেনারেটেড ছবি নয়)।
         No About page exists — primary CTA points to categories.index. --}}
    <x-our-story
            :title="__('home.story.title')"
            :subtitle="__('home.story.subtitle')"
            :description="__('home.story.description')"
            :image-alt="__('home.story.image_alt')"
            :cta-url="route('categories.index')"
            :cta-label="__('home.story.cta_about')"
            :products-url="route('products.index')"
            :products-label="__('home.story.cta_products')" />
</x-layouts.app>
