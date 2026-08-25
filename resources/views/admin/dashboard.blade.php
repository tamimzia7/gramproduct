<x-admin.layout title="অ্যাডমিন ড্যাশবোর্ড">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">ড্যাশবোর্ড</h1>
            <p class="text-muted mb-0">স্বাগতম, {{ auth()->user()->name }}</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted">লগইন করেছেন</h2>
                    <p class="mb-0">{{ auth()->user()->name }}</p>
                    <p class="text-muted small mb-0">{{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.categories.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 bg-success-subtle">
                    <div class="card-body">
                        <h2 class="h6 text-muted">ক্যাটাগরি</h2>
                        <p class="mb-0 fw-semibold">
                            <i class="bi bi-tags me-2"></i>ক্যাটাগরি পরিচালনা করুন
                        </p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-admin.layout>
