@props(['title' => 'অ্যাডমিন প্যানেল'])

<x-layouts.app :title="$title">
    <div class="container-fluid py-4">
        <div class="row">
            {{-- অ্যাডমিন সাইডবার --}}
            <div class="col-lg-2 d-none d-lg-block">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-3 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">অ্যাডমিন মেনু</h6>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>ড্যাশবোর্ড
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.categories.index') }}">
                                <i class="bi bi-tags me-2"></i>ক্যাটাগরি
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.products.index') }}">
                                <i class="bi bi-box-seam me-2"></i>পণ্যসমূহ
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.inventory.index') }}">
                                <i class="bi bi-clipboard-data me-2"></i>ইনভেন্টরি
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- মূল কনটেন্ট --}}
            <div class="col-lg-10">
                {{-- মোবাইল অ্যাডমিন নেভ --}}
                <div class="d-lg-none mb-3">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('admin.dashboard') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.dashboard') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-speedometer2 me-1"></i>ড্যাশবোর্ড
                        </a>
                        <a href="{{ route('admin.categories.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.categories.*') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-tags me-1"></i>ক্যাটাগরি
                        </a>
                        <a href="{{ route('admin.products.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.products.*') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-box-seam me-1"></i>পণ্য
                        </a>
                        <a href="{{ route('admin.inventory.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.inventory.*') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-clipboard-data me-1"></i>স্টক
                        </a>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.app>
