<x-layouts.app title="Dashboard">
    <div class="container py-5">
        <h1 class="h3 mb-4">Welcome, {{ auth()->user()->name }}</h1>

        @if (! auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning">
                Your email address is not verified.
                <a href="{{ route('verification.notice') }}" class="alert-link">Verify now</a>.
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">Account</h2>
                        <p class="mb-0">{{ auth()->user()->email }}</p>
                        <p class="text-muted small mb-0">{{ auth()->user()->phone ?? 'No phone added' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">Roles</h2>
                        <p class="mb-0">
                            @if (auth()->user()->hasAnyRole())
                                {{ auth()->user()->roles->pluck('name')->join(', ') }}
                            @else
                                Customer
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">Status</h2>
                        <p class="mb-0">
                            <span class="badge {{ auth()->user()->isActive() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ auth()->user()->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
