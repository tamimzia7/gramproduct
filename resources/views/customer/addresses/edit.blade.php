<x-layouts.app title="ঠিকানা সম্পাদনা">
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
                        <h1 class="h4 mb-4">ঠিকানা সম্পাদনা করুন</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('customer.addresses.update', $address) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="label" class="form-label">লেবেল</label>
                                    <input type="text" id="label" name="label"
                                           class="form-control @error('label') is-invalid @enderror"
                                           value="{{ old('label', $address->label) }}" placeholder="বাসা, অফিস ইত্যাদি">
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="type" class="form-label">ধরন *</label>
                                    <select id="type" name="type"
                                            class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="shipping" {{ old('type', $address->type) === 'shipping' ? 'selected' : '' }}>শিপিং</option>
                                        <option value="billing" {{ old('type', $address->type) === 'billing' ? 'selected' : '' }}>বিলিং</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="name" class="form-label">নাম *</label>
                                    <input type="text" id="name" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $address->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">ফোন নম্বর</label>
                                    <input type="text" id="phone" name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $address->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address_line_1" class="form-label">ঠিকানা লাইন ১ *</label>
                                    <input type="text" id="address_line_1" name="address_line_1"
                                           class="form-control @error('address_line_1') is-invalid @enderror"
                                           value="{{ old('address_line_1', $address->address_line_1) }}" required>
                                    @error('address_line_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address_line_2" class="form-label">ঠিকানা লাইন ২</label>
                                    <input type="text" id="address_line_2" name="address_line_2"
                                           class="form-control @error('address_line_2') is-invalid @enderror"
                                           value="{{ old('address_line_2', $address->address_line_2) }}">
                                    @error('address_line_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="city" class="form-label">শহর *</label>
                                    <input type="text" id="city" name="city"
                                           class="form-control @error('city') is-invalid @enderror"
                                           value="{{ old('city', $address->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="state" class="form-label">রাজ্য/বিভাগ</label>
                                    <input type="text" id="state" name="state"
                                           class="form-control @error('state') is-invalid @enderror"
                                           value="{{ old('state', $address->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="postal_code" class="form-label">পোস্টাল কোড</label>
                                    <input type="text" id="postal_code" name="postal_code"
                                           class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code', $address->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="country" class="form-label">দেশ *</label>
                                    <input type="text" id="country" name="country"
                                           class="form-control @error('country') is-invalid @enderror"
                                           value="{{ old('country', $address->country) }}" required>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_default" name="is_default"
                                               class="form-check-input"
                                               value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">
                                            ডিফল্ট ঠিকানা হিসেবে সেট করুন
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success">ঠিকানা আপডেট করুন</button>
                                        <a href="{{ route('customer.addresses.index') }}" class="btn btn-outline-secondary">বাতিল</a>
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
