<x-layouts.app title="ঠিকানা">
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h4 mb-0">আমার ঠিকানা</h1>
                            <a href="{{ route('customer.addresses.create') }}" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>নতুন ঠিকানা
                            </a>
                        </div>

                        @if ($addresses->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-geo-alt fs-1 text-muted"></i>
                                <p class="text-muted mt-3">আপনার কোনো ঠিকানা নেই।</p>
                                <a href="{{ route('customer.addresses.create') }}" class="btn btn-success">
                                    প্রথম ঠিকানা যোগ করুন
                                </a>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($addresses as $address)
                                    <div class="col-md-6">
                                        <div class="card h-100 {{ $address->is_default ? 'border-success' : '' }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        @if ($address->label)
                                                            <span class="badge bg-success-subtle text-success">
                                                                {{ $address->label }}
                                                            </span>
                                                        @endif
                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                            {{ $address->type === 'billing' ? 'বিলিং' : 'শিপিং' }}
                                                        </span>
                                                        @if ($address->is_default)
                                                            <span class="badge bg-success">ডিফল্ট</span>
                                                        @endif
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary" type="button"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="{{ route('customer.addresses.edit', $address) }}">
                                                                    <i class="bi bi-pencil me-2"></i>সম্পাদনা
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <form method="POST"
                                                                      action="{{ route('customer.addresses.destroy', $address) }}"
                                                                      onsubmit="return confirm('আপনি কি নিশ্চিত এই ঠিকানা মুছে ফেলতে চান?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                            class="dropdown-item text-danger">
                                                                        <i class="bi bi-trash me-2"></i>মুছুন
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <h3 class="h6 mb-1">{{ $address->name }}</h3>
                                                @if ($address->phone)
                                                    <p class="text-muted small mb-1">{{ $address->phone }}</p>
                                                @endif
                                                <p class="small mb-0">
                                                    {{ $address->address_line_1 }}
                                                    @if ($address->address_line_2)
                                                        , {{ $address->address_line_2 }}
                                                    @endif
                                                    <br>
                                                    {{ $address->city }}
                                                    @if ($address->state)
                                                        , {{ $address->state }}
                                                    @endif
                                                    @if ($address->postal_code)
                                                        - {{ $address->postal_code }}
                                                    @endif
                                                    <br>
                                                    {{ $address->country }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
