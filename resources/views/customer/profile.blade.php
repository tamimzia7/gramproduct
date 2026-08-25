<x-layouts.app title="প্রোফাইল">
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
                        <h1 class="h4 mb-4">প্রোফাইল</h1>

                        <form method="POST" action="{{ route('customer.profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">পূর্ণ নাম</label>
                                    <input type="text" id="name" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">ইমেইল ঠিকানা</label>
                                    <input type="email" id="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">ফোন নম্বর</label>
                                    <input type="text" id="phone" name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small mb-0">
                                                ঠিকানা: {{ $addressCount }}টি
                                            </p>
                                        </div>
                                        <button type="submit" class="btn btn-success">সংরক্ষণ করুন</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
