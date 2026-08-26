<x-layouts.app
    :title="__('home.meta.title')"
    :meta-description="__('home.meta.description')">

    {{-- ============================== Hero ============================== --}}
    <section class="bg-success-subtle py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold text-success-emphasis">
                        {{ __('home.hero.title') }}
                    </h1>
                    <p class="lead mt-3 mb-4">
                        {{ __('home.hero.subtitle') }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-basket2 me-2"></i>{{ __('home.hero.cta_primary') }}
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-success btn-lg px-4">
                            {{ __('home.hero.cta_secondary') }}
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="{{ config('app.name', 'Gram Product') }} লোগো"
                         class="img-fluid rounded-3 shadow-sm bg-white p-3"
                         style="max-height: 240px; width: auto;"
                         width="240" height="240">
                </div>
            </div>
        </div>
    </section>

    {{-- ======================= Quick Categories ======================= --}}
    @if ($quickCategories->isNotEmpty())
        <section class="py-5">
            <div class="container">
                <x-section-header
                        :title="__('home.quick_categories.title')"
                        :view-all-url="route('categories.index')"
                        :view-all-text="__('home.quick_categories.view_all')" />
                <div class="row g-3">
                    @foreach ($quickCategories as $category)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('categories.show', $category) }}"
                               class="text-decoration-none h-100 d-block"
                               aria-label="{{ $category->name }}">
                                <div class="card h-100 border-0 shadow-sm text-center category-card">
                                    <div class="card-body py-4">
                                        @if ($category->image)
                                            <img src="{{ asset('storage/'.$category->image) }}"
                                                 alt="{{ $category->name }}" loading="lazy"
                                                 class="rounded-circle mb-2 object-fit-cover"
                                                 style="width: 64px; height: 64px;">
                                        @else
                                            <div class="fs-1 mb-2" aria-hidden="true">🧺</div>
                                        @endif
                                        <h3 class="h6 mt-2 mb-1 text-body">{{ $category->name }}</h3>
                                        <small class="text-muted">
                                            {{ \App\Support\BengaliNumber::format($category->products_count) }} টি পণ্য
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ======================= Featured Products ======================= --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="py-5 pt-0">
            <div class="container">
                <x-section-header
                        :title="__('home.featured.title')"
                        :subtitle="__('home.featured.subtitle')"
                        :view-all-url="route('products.index', ['sort' => 'popular'])"
                        :view-all-text="__('home.featured.view_all')" />
                <x-product-grid :products="$featuredProducts" />
            </div>
        </section>
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

    {{-- ======================= Promotional Banner ======================= --}}
    <section class="py-5">
        <div class="container">
            <div class="rounded-4 p-4 p-md-5 text-white bg-success d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm">
                <div>
                    <h2 class="h4 fw-bold mb-1">{{ __('home.promo.title') }}</h2>
                    <p class="mb-0 opacity-75">{{ __('home.promo.subtitle') }}</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-light btn-lg text-success fw-semibold">
                    {{ __('home.promo.cta') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ======================= Why Choose Us ======================= --}}
    <section class="py-5 bg-light">
        <div class="container">
            <x-section-header :title="__('home.why.title')" :subtitle="__('home.why.subtitle')" />
            <div class="row g-4">
                @foreach (__('home.why.items') as $item)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-4">
                                <i class="bi {{ $item['icon'] }} fs-1 text-success"></i>
                                <h3 class="h5 mt-3">{{ $item['title'] }}</h3>
                                <p class="text-muted small mb-0">{{ $item['text'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======================= Trust / Benefits ======================= --}}
    <section class="py-4 border-top border-bottom bg-white">
        <div class="container">
            <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-4 mb-0">
                @foreach (__('home.trust.items') as $trust)
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success fs-5" aria-hidden="true"></i>
                        <span class="fw-semibold">{{ $trust }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============================== CTA ============================== --}}
    <section class="py-5">
        <div class="container text-center">
            <h2 class="h3 fw-bold">{{ __('home.cta.title') }}</h2>
            <p class="text-muted mt-2">{{ __('home.cta.subtitle') }}</p>
            <a href="{{ route('products.index') }}" class="btn btn-success btn-lg px-5 mt-3">
                {{ __('home.cta.button') }}
            </a>
        </div>
    </section>
</x-layouts.app>
