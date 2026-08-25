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
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">পণ্যসমূহ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">ক্যাটাগরি</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    @php
                        $wishlistCount = \App\Models\WishlistItem::where('user_id', auth()->id())->count();
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('wishlist.index') }}" id="wishlist-link">
                            <i class="bi bi-heart fs-5"></i>
                            @if ($wishlistCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge"
                                      style="font-size:0.65rem;">
                                    {{ $wishlistCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endauth
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">লগইন</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">রেজিস্টার</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">ড্যাশবোর্ড</a>
                    </li>
                    @if (auth()->user()->hasAnyRole())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">অ্যাডমিন</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-white p-0 ms-lg-2">লগআউট</button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
