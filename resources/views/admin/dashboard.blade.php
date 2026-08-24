<x-layouts.app title="Admin Dashboard">
    <div class="container py-5">
        <h1 class="h3 mb-4">Admin Dashboard</h1>

        <div class="alert alert-info">
            The admin foundation is in place. Role &amp; permission management and module administration
            will be built in the Admin module phase.
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">Logged in as</h2>
                        <p class="mb-0">{{ auth()->user()->name }}</p>
                        <p class="text-muted small mb-0">{{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
