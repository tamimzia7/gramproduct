@props(['title' => 'অ্যাডমিন প্যানেল', 'breadcrumbs' => []])

@php
    $adminUser = auth()->user();
    $menuService = app(\App\Services\Admin\AdminMenuService::class);
    $menuGroups = $menuService->forUser($adminUser);
    $defaultBreadcrumbs = [['label' => 'অ্যাডমিন', 'url' => route('admin.dashboard')], ['label' => $title]];
    $crumbs = $breadcrumbs ?: $defaultBreadcrumbs;

    // প্রতিটি মেনু আইটেমে URL প্রি-কম্পিউট করে রাখি; রুট না থাকলে বাদ দিই
    $prepareMenu = function (array $groups) use (&$prepareMenu): array {
        $result = [];
        foreach ($groups as $group) {
            $items = [];
            foreach ($group['items'] as $item) {
                if (! \Illuminate\Support\Facades\Route::has($item['route'])) {
                    continue;
                }
                $items[] = array_merge($item, [
                    'url' => route($item['route'], $item['params'] ?? []),
                ]);
            }
            if ($items !== []) {
                $result[] = ['label' => $group['label'], 'items' => $items];
            }
        }
        return $result;
    };
    $menuGroups = $prepareMenu($menuGroups);
@endphp

<x-layouts.admin :title="$title">
    <div class="admin-shell">
        {{-- সাইডবার --}}
        <aside class="admin-sidebar" id="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand">
                <span class="admin-sidebar__brand-logo">গ</span>
                <span class="admin-sidebar__brand-text">{{ config('app.name', 'Gram Product') }} <small class="d-block" style="font-size:0.7rem;font-weight:400;opacity:.7;">অ্যাডমিন</small></span>
            </a>

            <nav class="admin-sidebar__nav">
                @foreach ($menuGroups as $group)
                    @if ($loop->first && $group['label'] === 'ড্যাশবোর্ড')
                        @foreach ($group['items'] as $item)
                            <a href="{{ $item['url'] }}"
                               class="admin-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                                <span class="admin-nav-text">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                        @continue
                    @endif

                    <span class="admin-nav-section-label">{{ $group['label'] }}</span>
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['url'] }}"
                           class="admin-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span class="admin-nav-text">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <div class="admin-sidebar__footer px-3 py-3 border-top" style="border-color:rgba(255,255,255,.08)!important;">
                <div class="d-flex align-items-center gap-2 admin-sidebar__footer-text">
                    <span class="admin-avatar">{{ mb_substr($adminUser->name, 0, 1) }}</span>
                    <div class="lh-sm">
                        <div class="text-white" style="font-size:.85rem;">{{ $adminUser->name }}</div>
                        <div style="font-size:.72rem;opacity:.7;">অ্যাডমিন</div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- মোবাইল ওভারলে --}}
        <div class="admin-sidebar-overlay" id="admin-sidebar-overlay"></div>

        {{-- মূল কনটেন্ট --}}
        <div class="admin-main">
            {{-- টপবার --}}
            <header class="admin-topbar">
                <button class="btn btn-light border d-md-none" id="admin-sidebar-toggle-mobile" type="button" aria-label="মেনু খুলুন">
                    <i class="bi bi-list"></i>
                </button>
                <button class="btn btn-light border d-none d-md-inline-flex" id="admin-sidebar-collapser" type="button" aria-label="সাইডবার ভাঁজ করুন">
                    <i class="bi bi-layout-sidebar"></i>
                </button>

                <h1 class="admin-topbar__title">{{ $title }}</h1>

                <div class="ms-auto d-flex align-items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="সাইট দেখুন">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm d-flex align-items-center gap-2 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="admin-avatar">{{ mb_substr($adminUser->name, 0, 1) }}</span>
                            <span class="d-none d-sm-inline">{{ $adminUser->name }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                                    <i class="bi bi-person me-1"></i> আমার প্রোফাইল
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i> লগআউট
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            {{-- কনটেন্ট --}}
            <main class="admin-content">
                {{-- ব্রেডক্রাম্ব --}}
                <nav aria-label="breadcrumb" class="admin-breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
                        @foreach ($crumbs as $crumb)
                            @if ($loop->last)
                                <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a></li>
                            @endif
                        @endforeach
                    </ol>
                </nav>

                {{-- ফ্ল্যাশ মেসেজ --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show admin-alert admin-alert-auto" role="alert">
                        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show admin-alert admin-alert-auto" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger admin-alert" role="alert">
                        <strong>দয়া করে ফর্মের ত্রুটিগুলো ঠিক করুন:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.admin>
