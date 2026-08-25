<x-layouts.app title="অর্ডার ইতিহাস">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 text-muted mb-3">অ্যাকাউন্ট মেনু</h2>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}"
                               href="{{ route('customer.profile') }}">
                                <i class="bi bi-person me-2"></i>প্রোফাইল
                            </a>
                            <a class="nav-link {{ request()->routeIs('customer.addresses.*') ? 'active' : '' }}"
                               href="{{ route('customer.addresses.index') }}">
                                <i class="bi bi-geo-alt me-2"></i>ঠিকানা
                            </a>
                            <a class="nav-link {{ request()->routeIs('customer.order-history') ? 'active' : '' }}"
                               href="{{ route('customer.order-history') }}">
                                <i class="bi bi-bag me-2"></i>অর্ডার ইতিহাস
                            </a>
                            <a class="nav-link {{ request()->routeIs('customer.settings') ? 'active' : '' }}"
                               href="{{ route('customer.settings') }}">
                                <i class="bi bi-gear me-2"></i>অ্যাকাউন্ট সেটিংস
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-4">অর্ডার ইতিহাস</h1>

                        <div class="text-center py-5">
                            <i class="bi bi-bag fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-2">অর্ডার ইতিহাস শীঘ্রই যুক্ত হবে।</p>
                            <p class="text-muted small">
                                অর্ডার মডিউল পরবর্তী পর্যায়ে বাস্তবায়ন করা হবে।
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
