<x-layouts.app title="ড্যাশবোর্ড">
    <div class="container py-5">
        <h1 class="h3 mb-4">স্বাগতম, {{ auth()->user()->name }}</h1>

        @if (! auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning">
                আপনার ইমেইল ঠিকানা এখনো যাচাই করা হয়নি।
                <a href="{{ route('verification.notice') }}" class="alert-link">এখনই যাচাই করুন</a>।
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">অ্যাকাউন্ট</h2>
                        <p class="mb-0">{{ auth()->user()->email }}</p>
                        <p class="text-muted small mb-0">{{ auth()->user()->phone ?? 'ফোন নম্বর যোগ করা হয়নি' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">ভূমিকা</h2>
                        <p class="mb-0">
                            @if (auth()->user()->hasAnyRole())
                                {{ auth()->user()->roles->pluck('name')->join(', ') }}
                            @else
                                কাস্টমার
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">স্ট্যাটাস</h2>
                        <p class="mb-0">
                            <span class="badge {{ auth()->user()->isActive() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ auth()->user()->isActive() ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
