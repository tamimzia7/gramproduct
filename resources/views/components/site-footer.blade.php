@php($footerHeadingClass = 'site-footer-heading')

<footer class="site-footer bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            {{-- ব্র্যান্ড + সোশ্যাল --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="{{ config('app.name', 'Gram Product') }} লোগো"
                         style="height: 32px; width: auto;"
                         loading="lazy">
                    <span class="fw-bold fs-5 text-white">{{ config('app.name', 'Gram Product') }}</span>
                </div>
                <p class="small text-secondary mb-3">
                    {{ __('footer.brand_description') }}
                </p>

                @if (! empty($footerSocialItems))
                    <nav class="site-footer-social d-flex gap-2" aria-label="সোশ্যাল লিংক">
                        @foreach ($footerSocialItems as $social)
                            <a href="{{ $social['href'] }}"
                               class="site-footer-social-link"
                               aria-label="{{ $social['aria'] }}"
                               target="_blank"
                               rel="noopener">
                                <i class="bi {{ $social['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </nav>
                @endif
            </div>

            {{-- দ্রুত লিংক --}}
            <nav class="col-6 col-md-3 col-lg-2" aria-label="{{ __('footer.quick_links') }}">
                <h3 class="{{ $footerHeadingClass }}">{{ __('footer.quick_links') }}</h3>
                <ul class="list-unstyled small mb-0">
                    @foreach ($footerQuickLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}"
                               class="link-light text-decoration-none d-inline-block py-1">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- পণ্যসমূহ (ডায়নামিক ক্যাটাগরি) --}}
            <nav class="col-6 col-md-3 col-lg-3" aria-label="{{ __('footer.products') }}">
                <h3 class="{{ $footerHeadingClass }}">{{ __('footer.products') }}</h3>
                <ul class="list-unstyled small mb-0">
                    @foreach ($footerCategories as $category)
                        <li>
                            <a href="{{ route('categories.show', ['category' => $category['slug']]) }}"
                               class="link-light text-decoration-none d-inline-block py-1">
                                {{ $category['name'] }}
                            </a>
                        </li>
                    @endforeach

                    @if ($footerHasMoreCategories)
                        <li>
                            <a href="{{ route('categories.index') }}"
                               class="text-success text-decoration-none d-inline-block py-1">
                                {{ __('footer.categories_view_all') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>

            {{-- যোগাযোগ --}}
            <div class="col-12 col-lg-3">
                <h3 class="{{ $footerHeadingClass }}">{{ __('footer.contact') }}</h3>
                @if (count($footerContactActions) > 0 || $footerAddress !== null && $footerAddress !== '')
                    <ul class="list-unstyled small mb-0">
                        @foreach ($footerContactActions as $action)
                            <li class="py-1">
                                <a href="{{ $action['href'] }}"
                                   class="link-light text-decoration-none d-inline-flex align-items-center gap-2"
                                   @if (! empty($action['external'])) target="_blank" rel="noopener" @endif
                                   aria-label="{{ $action['aria'] }}">
                                    <i class="bi {{ $action['icon'] }}" aria-hidden="true"></i>
                                    {{ isset($action['display']) ? \App\Support\BengaliNumber::format($action['display']) : $action['label'] }}
                                </a>
                            </li>
                        @endforeach

                        @if ($footerAddress !== null && $footerAddress !== '')
                            <li class="py-1">
                                <address class="text-secondary m-0 d-inline-flex align-items-center gap-2" style="font-style: normal;">
                                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                    {{ $footerAddress }}
                                </address>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>

        <hr class="site-footer-divider my-4">

        <div class="text-center">
            <p class="small text-secondary mb-0">
                © @bn(now()->year) {{ config('app.name', 'Gram Product') }}। {{ __('footer.copyright_suffix') }}
            </p>
        </div>
    </div>
</footer>