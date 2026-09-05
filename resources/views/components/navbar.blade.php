@php
    $contactPhone = config('shop.contact.phone');
    $contactAddress = config('shop.contact.address');
    $currentCategory = request()->input('category');
@endphp

<header class="site-header sticky-top" id="siteHeader">
    {{-- ============================ শীর্ষ সবুজ তথ্য বার ============================ --}}
    <div class="site-topbar d-none d-lg-block">
        <div class="container d-flex align-items-center justify-content-between gap-3 py-1">
            <ul class="list-inline mb-0 d-flex flex-wrap align-items-center gap-3">
                @if ($contactAddress)
                    <li class="list-inline-item topbar-item">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <span>{{ $contactAddress }}</span>
                    </li>
                @endif
                <li class="list-inline-item topbar-item">
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <span>{{ $contactPhone ? 'হটলাইন: '.$contactPhone : 'সরাসরি হটলাইনে কথা বলুন' }}</span>
                </li>
                <li class="list-inline-item topbar-item">
                    <i class="bi bi-truck" aria-hidden="true"></i>
                    <span>সারাদেশে হোম ডেলিভারি</span>
                </li>
                <li class="list-inline-item topbar-item">
                    <i class="bi bi-bag-check" aria-hidden="true"></i>
                    <span>অর্ডার করুন</span>
                </li>
            </ul>

            <ul class="list-inline mb-0 d-flex align-items-center gap-3">
                <li class="list-inline-item topbar-item">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>প্রয়োজনীয় পণ্য খুঁজে নিন</span>
                </li>
                <li class="list-inline-item topbar-item">
                    <i class="bi bi-question-circle" aria-hidden="true"></i>
                    <span>সাহায্য সহায়তা</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- ============================ মূল হেডার ============================ --}}
    <div class="site-main-header bg-white">
        <div class="container py-3">
            <div class="row g-2 g-lg-3 align-items-center">
                {{-- লোগো --}}
                <div class="col-6 col-lg-3">
                    <a class="d-inline-flex align-items-center gap-2 text-decoration-none"
                       href="{{ route('home') }}" aria-label="{{ config('app.name', 'গ্রামপ্রোডাক্ট') }}">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="{{ config('app.name', 'গ্রামপ্রোডাক্ট') }} লোগো"
                             class="site-logo"
                             style="height: 44px; width: auto; object-fit: contain;"
                             loading="eager">
                    </a>
                </div>

                {{-- অনুসন্ধান বক্স — ডেস্কটপে মাঝে, মোবাইলে পরবর্তী সারিতে --}}
                <div class="col-12 order-last col-lg-6 order-lg-0 pt-2 pt-lg-0">
                    <form class="site-search" role="search" method="GET" action="{{ route('products.index') }}">
                        <label for="header-search" class="visually-hidden">পণ্য খুঁজুন</label>

                        <div class="site-search__inner">
                            <select class="site-search__category form-select" name="category" aria-label="ক্যাটাগরি">
                                <option value="">সব ক্যাটাগরি</option>
                                @foreach ($topCategories as $topCategory)
                                    <option value="{{ $topCategory->slug }}"
                                            @selected((string) $currentCategory === $topCategory->slug)>
                                        {{ $topCategory->name }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="search" id="header-search" name="q"
                                   class="site-search__input form-control"
                                   placeholder="{{ request()->has('q') ? '' : 'চাল, মাছ, সবজি খুঁজুন...' }}"
                                   value="{{ request('q') }}"
                                   autocomplete="off"
                                   aria-label="পণ্য খুঁজুন">

                            <button type="submit" class="btn site-search__submit" aria-label="অনুসন্ধান করুন">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ডান পাশ — ইচ্ছেতালিকা, কার্ট, অ্যাকাউন্ট --}}
                <div class="col-6 col-lg-3 d-flex align-items-center justify-content-end gap-1 gap-lg-2">
                    <a href="{{ route('wishlist.index') }}" class="site-action d-inline-flex"
                       aria-label="আমার ইচ্ছেতালিকা" title="ইচ্ছেতালিকা">
                        <span class="position-relative">
                            <i class="bi bi-heart fs-4"></i>
                            @if ($wishlistCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge">
                                    {{ \App\Support\BengaliNumber::format($wishlistCount) }}
                                </span>
                            @endif
                        </span>
                    </a>

                    <a href="{{ route('cart.index') }}" class="site-action d-flex align-items-center gap-2"
                       aria-label="আমার কার্ট" title="কার্ট">
                        <span class="position-relative">
                            <i class="bi bi-cart3 fs-4"></i>
                            @if ($cartCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count-badge">
                                    {{ \App\Support\BengaliNumber::format($cartCount) }}
                                </span>
                            @endif
                        </span>
                        <span class="site-action__text d-none d-md-block lh-sm">
                            <span class="site-action__title d-block">কার্ট</span>
                            <span class="site-action__sub d-block site-cart-total">
                                @if ($cartCount > 0)
                                    {{ \App\Support\BengaliNumber::money($cartTotal) }}
                                @else
                                    খালি
                                @endif
                            </span>
                        </span>
                    </a>

                    {{-- অ্যাকাউন্ট ড্রপডাউন --}}
                    <div class="dropdown">
                        <button class="btn site-action site-action__btn dropdown-toggle d-flex align-items-center gap-2"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                aria-label="আমার অ্যাকাউন্ট">
                            <i class="bi bi-person-circle fs-4"></i>
                            <span class="site-action__text d-none d-md-block text-start lh-sm">
                                <span class="site-action__title d-block">{{ auth()->check() ? auth()->user()->name : 'আমার অ্যাকাউন্ট' }}</span>
                                <span class="site-action__sub d-block">{{ auth()->check() ? 'ড্যাশবোর্ড' : 'লগইন / রেজিস্টার' }}</span>
                            </span>
                            <i class="bi bi-chevron-down d-none d-md-inline" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end account-menu">
                            @auth
                                @if (auth()->user()->hasAnyRole())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>অ্যাডমিন প্যানেল</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-person me-2"></i>ড্যাশবোর্ড</a></li>
                                <li><a class="dropdown-item" href="{{ route('cart.index') }}"><i class="bi bi-cart3 me-2"></i>আমার কার্ট</a></li>
                                <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="bi bi-heart me-2"></i>ইচ্ছেতালিকা</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>লগআউট</button>
                                    </form>
                                </li>
                            @else
                                <li>
                                    <a class="dropdown-item" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>লগইন
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('register') }}">
                                        <i class="bi bi-person-plus me-2"></i>রেজিস্টার
                                    </a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================ নেভিগেশন সারি ============================ --}}
    <nav class="site-navbar" aria-label="প্রধান মেনু">
        <div class="container d-flex align-items-center gap-3">
            {{-- সব ক্যাটাগরি বোতাম --}}
            <div class="dropdown site-categories-dd">
                <button class="btn site-categories-btn dropdown-toggle d-flex align-items-center gap-2"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        aria-label="সব ক্যাটাগরি">
                    <i class="bi bi-list fs-5"></i>
                    <span>সব ক্যাটাগরি</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </button>

                @if ($navCategories->isNotEmpty())
                    <ul class="dropdown-menu site-categories-menu">
                        <li>
                            <a class="dropdown-item fw-medium" href="{{ route('categories.index') }}">
                                <i class="bi bi-grid me-2"></i>সব ক্যাটাগরি দেখুন
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach ($navCategories as $navCategory)
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center"
                                   href="{{ route('categories.show', $navCategory) }}">
                                    <span>
                                        <i class="bi bi-tag me-2"></i>{{ $navCategory->name }}
                                    </span>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- মেনু আইটেম — ডেস্কটপে সম্পূর্ণ, মোবাইলে হরাইজন্টাল স্ক্রল --}}
            <ul class="site-nav list-unstyled mb-0 d-flex align-items-center gap-1 flex-shrink-1 overflow-auto">
                <li>
                    <a class="site-nav__link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        হোম
                    </a>
                </li>
                <li>
                    <a class="site-nav__link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                        দোকান
                    </a>
                </li>
                <li>
                    <a class="site-nav__link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                        ক্যাটাগরি
                    </a>
                </li>
                <li>
                    <a class="site-nav__link" href="{{ route('products.index') }}">ছাড়/অফার</a>
                </li>
                <li>
                    <a class="site-nav__link" href="{{ route('categories.index') }}">আমাদের সম্পর্কে</a>
                </li>
                <li>
                    <a class="site-nav__link" href="{{ route('products.index') }}">ব্লগ</a>
                </li>
                <li>
                    <a class="site-nav__link" href="{{ route('categories.index') }}">যোগাযোগ</a>
                </li>
            </ul>
        </div>
    </nav>
</header>