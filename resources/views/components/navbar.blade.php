<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            {{ config('app.name', 'Gram Product') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">হোম</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">ক্যাটাগরি</a>
                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                        @if (isset($navCategories) && $navCategories->isNotEmpty())
                            @foreach ($navCategories as $navCategory)
                                <li>
                                    <a class="dropdown-item" href="{{ route('categories.show', $navCategory->slug) }}">
                                        {{ $navCategory->name }}
                                    </a>
                                </li>
                                @foreach ($navCategory->children as $child)
                                    <li>
                                        <a class="dropdown-item ps-4" href="{{ route('categories.show', $child->slug) }}">
                                            &mdash; {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @endforeach
                        @else
                            <li><span class="dropdown-item text-muted">কোনো ক্যাটাগরি নেই</span></li>
                        @endif
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">পণ্যসমূহ</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">লগইন</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">রেজিস্টার</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('customer.*') ? 'active' : '' }}" href="#"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                    <i class="bi bi-person me-2"></i>প্রোফাইল
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('customer.addresses.index') }}">
                                    <i class="bi bi-geo-alt me-2"></i>ঠিকানা
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('customer.order-history') }}">
                                    <i class="bi bi-bag me-2"></i>অর্ডার ইতিহাস
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('customer.settings') }}">
                                    <i class="bi bi-gear me-2"></i>সেটিংস
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2 me-2"></i>ড্যাশবোর্ড
                                </a>
                            </li>
                            @if (auth()->user()->hasAnyRole())
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-shield-lock me-2"></i>অ্যাডমিন
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>লগআউট
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
