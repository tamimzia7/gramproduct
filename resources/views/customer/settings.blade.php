<x-layouts.app title="অ্যাকাউন্ট সেটিংস">
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
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-4">পাসওয়ার্ড পরিবর্তন করুন</h1>

                        <form method="POST" action="{{ route('customer.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="current_password" class="form-label">বর্তমান পাসওয়ার্ড *</label>
                                    <input type="password" id="current_password" name="current_password"
                                           class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6"></div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">নতুন পাসওয়ার্ড *</label>
                                    <input type="password" id="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">পাসওয়ার্ড নিশ্চিত করুন *</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">পাসওয়ার্ড পরিবর্তন করুন</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
