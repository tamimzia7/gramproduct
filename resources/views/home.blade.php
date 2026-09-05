<x-layouts.app
    :title="__('home.meta.title')"
    :meta-description="__('home.meta.description')">

    @push('head')
        {{-- Hero-র মূল ছবি above-the-fold — lazy load নয়, eager preload --}}
        <link rel="preload" as="image" href="{{ asset('images/b-image3.jpg') }}" fetchpriority="high">
        <link rel="preload" as="image" href="{{ asset('images/b-image1.jpg') }}" fetchpriority="high">
    @endpush

    {{-- ============================== Hero ==============================
         পূর্ণ-প্রস্থ ধানক্ষেতের real ফটোগ্রাফি (b-image3) — ব্যাকগ্রাউন্ডে;
         ডান পাশে কৃষকের real ছবি (b-image1, field-এ masked/blended);
         বামে বড় বাংলা হেডিং + rounded CTA জোড়া (reference অনুযায়ী)। --}}
    <section class="hero-scene">
        <div class="hero-banner__bg" aria-hidden="true"></div>

        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <span class="d-block">{{ __('home.hero.title') }}</span>
                            <span class="d-block hero-title__green">{{ __('home.hero.title_highlight') }}</span>
                        </h1>
                        <p class="hero-subtitle">
                            {{ __('home.hero.subtitle') }}<br>
                            {{ __('home.hero.subtitle_2') }}
                        </p>
                        <div class="hero-actions">
                            <a href="{{ route('products.index') }}" class="btn hero-cta-btn hero-cta-btn--primary">
                                <i class="bi bi-bag-check me-2" aria-hidden="true"></i>{{ __('home.hero.cta_primary') }}
                            </a>
                            <a href="{{ route('categories.index') }}" class="btn hero-cta-btn hero-cta-btn--secondary">
                                <i class="bi bi-play-circle me-2" aria-hidden="true"></i>{{ __('home.hero.cta_secondary') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="hero-figure">
                        <img src="{{ asset('images/b-image1.jpg') }}"
                             alt="{{ __('home.hero.fig_alt') }}"
                             loading="eager"
                             fetchpriority="high">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== Feature/Benefit Strip ====================
         Hero-র ঠিক নিচে ৫টি benefit item — সাদা rounded container-এ।
         Icon (green line icon) + Bengali title + short description;
         reference-এর মতোই subtle divider-সহ এক সারি। --}}
    <section class="benefit-strip" aria-label="{{ __('home.features.aria') }}">
        <div class="container">
            <div class="benefit-strip__inner">
                @php
                    $featureIcons = ['bi-leaf', 'bi-tree', 'bi-truck', 'bi-box-seam', 'bi-person-raised-hand'];
                @endphp

                @foreach (__('home.features.items') as $index => $feature)
                    <div class="benefit-item">
                        <i class="bi {{ $featureIcons[$index] }} benefit-item__icon" aria-hidden="true"></i>
                        <div class="benefit-item__text">
                            <span class="benefit-item__title">{{ $feature['title'] }}</span>
                            <span class="benefit-item__desc">{{ $feature['description'] }}</span>
                        </div>
                    </div>
                @endforeach
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

    {{-- ==================== Customer Reviews / Testimonials ====================
         শুধুই বাস্তব অ্যাপ্রুভড রিভিউ — কোনো ফেক তথ্য নেই। রিভিউ সিস্টেম না
         থাকায় (অথবা অ্যাপ্রুভড রিভিউ না থাকায়) সম্পূর্ণ সেকশনটা বাদ যায়। --}}
    @if ($reviews->isNotEmpty())
        <x-testimonials-section :reviews="$reviews" />
    @endif

    {{-- ==================== How It Works / Order Process ====================
         "কীভাবে অর্ডার করবেন?" — ৪টি সহজ ধাপ। স্ট্যাটিক lang-ড্রiven
         (কোনো DB/JS নেই); ডেলিভারি সময়ের প্রতিশ্রুতি দেওয়া হয় না। --}}
    <x-order-process />

    {{-- ==================== Delivery Information ====================
         "আপনার ঠিকানায় পণ্য পৌঁছে দিই" — স্ট্যাটিক lang-ড্রiven
         (কোনো DB/JS নেই); ফেক জোন/চার্জ/সময়/ট্র্যাকিং দেখানো হয় না। --}}
    <x-delivery-info-section />

    {{-- ==================== Special Offers ====================
         "বিশেষ অফার" — সক্রিয় ছাড়যুক্ত পণ্য (ডেটাবেস-চালিত)।
         কোনো ছাড়যুক্ত পণ্য না থাকলে পুরো সেকশন বাদ যায় (কোনো ফেক অফার নেই)। --}}
    @if ($offerProducts->isNotEmpty())
        <x-special-offers-section :products="$offerProducts" />
    @endif

    {{-- ==================== Seasonal / Fresh Products ====================
         "এ সময়ের পণ্য" — অ্যাডমিন-চিহ্নিত মৌসুমি পণ্য (ডেটাবেস-চালিত)।
         কোনো মৌসুমি পণ্য না থাকলে পুরো সেকশন বাদ যায়। --}}
    @if ($seasonalProducts->isNotEmpty())
        <x-seasonal-products-showcase :products="$seasonalProducts" />
    @endif

    {{-- ==================== Popular Products ====================
         "জনপ্রিয় পণ্য" — বাস্তব ক্রয়-পরিমাণ (order_items) থেকে স্বয়ংক্রিয়।
         যথেষ্ট ক্রয় ডেটা না থাকলে পুরো সেকশন বাদ যায় (বানানো জনপ্রিয়তা নেই)। --}}
    @if ($popularProducts->isNotEmpty())
        <x-popular-products-section :products="$popularProducts" />
    @endif

    {{-- ==================== New Arrivals ====================
         "নতুন যোগ করা পণ্য" — সম্পূর্ণ স্বয়ংক্রিয় (সর্বশেষ active পণ্য)।
         কোনো পণ্য না থাকলে পুরো সেকশন বাদ যায়। --}}
    @if ($newProducts->isNotEmpty())
        <x-new-arrivals-section :products="$newProducts" />
    @endif

    {{-- ==================== Quick Contact CTA ====================
         "কোনো কিছু জানতে চান?" — শুধুমাত্র বাস্তব কনফিগার করা যোগাযোগ মাধ্যম
         (config/shop.php 'contact'). কিছুই সেট না থাকলে পুরো সেকশন লুকানো থাকে। --}}
    @if (count($contactActions) > 0)
        <x-contact-cta :actions="$contactActions" :contact-url="$contactUrl" />
    @endif
</x-layouts.app>
