<x-layouts.app title="মজুদ সমন্বয়">
    <div class="container py-4">
        <h1 class="h3 mb-4">মজুদ সমন্বয় করুন</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title h6 mb-3">বর্তমান মজুদ তথ্য</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-muted small">পণ্য</label>
                                <p class="fw-semibold mb-0">{{ $inventory->product->name }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">ভ্যারিয়েন্ট</label>
                                <p class="fw-semibold mb-0">{{ $inventory->variant?->name ?? '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">বর্তমান মজুদ</label>
                                <p class="fw-semibold mb-0">{{ $inventory->quantity }} {{ $inventory->product->unit }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">উপলব্ধ মজুদ</label>
                                <p class="fw-semibold mb-0">{{ $inventory->available_quantity }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">সংরক্ষিত</label>
                                <p class="fw-semibold mb-0">{{ $inventory->reserved_quantity }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">কম মজুদ সীমা</label>
                                <p class="fw-semibold mb-0">{{ $inventory->low_stock_threshold }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.inventories.adjust', $inventory) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">নতুন মজুদের পরিমাণ <span class="text-danger">*</span></label>
                                    <input type="number" id="quantity" name="quantity" min="0"
                                           class="form-control @error('quantity') is-invalid @enderror"
                                           value="{{ old('quantity', $inventory->quantity) }}" required>
                                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="reason" class="form-label">কারণ</label>
                                    <input type="text" id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror"
                                           value="{{ old('reason') }}" placeholder="যেমন: ম্যানুয়াল সমন্বয়">
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-success">সমন্বয় করুন</button>
                                <a href="{{ route('admin.inventories.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title h6 mb-3">মজুদ পরিসংখ্যান</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted small">মোট স্টক ইন</label>
                            <p class="fw-semibold mb-0 text-success">
                                +{{ $inventory->adjustments->where('type', 'stock_in')->sum('quantity') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">মোট স্টক আউট</label>
                            <p class="fw-semibold mb-0 text-danger">
                                {{ $inventory->adjustments->where('type', 'stock_out')->sum('quantity') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">মোট নষ্ট পণ্য</label>
                            <p class="fw-semibold mb-0 text-warning">
                                {{ $inventory->wasted_quantity }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">মোট ক্ষতিগ্রস্ত</label>
                            <p class="fw-semibold mb-0 text-secondary">
                                {{ $inventory->damaged_quantity }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
