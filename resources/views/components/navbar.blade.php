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
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">ড্যাশবোর্ড</a>
                    </li>
                    @if (auth()->user()->hasAnyRole())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">অ্যাডমিন</a>
                        </li>
                        @if (auth()->user()->can('manage-categories'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">ক্যাটাগরি ব্যবস্থাপনা</a>
                            </li>
                        @endif
                        @if (auth()->user()->can('manage-products'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">পণ্য ব্যবস্থাপনা</a>
                            </li>
                        @endif
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
