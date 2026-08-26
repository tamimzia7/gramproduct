<x-admin.layout title="{{ __('inventory.forms.adjust_title') }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('inventory.forms.adjust_title') }}</h1>
            <p class="text-muted mb-0">{{ $inventory->variant?->product?->name }} — {{ $inventory->variant?->name }}</p>
        </div>
        <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>ফিরে যান
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.inventory.adjust', $inventory) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="current_stock_display" class="form-label">{{ __('inventory.forms.current_stock') }}</label>
                        <input type="text" id="current_stock_display" class="form-control" disabled
                               value="{{ \App\Support\BengaliNumber::format($inventory->quantity) }}">
                        <div class="form-text">{{ __('inventory.fields.reserved_quantity') }}: {{ \App\Support\BengaliNumber::format($inventory->reserved_quantity) }} | {{ __('inventory.fields.available_quantity') }}: {{ \App\Support\BengaliNumber::format($inventory->available_quantity) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label">{{ __('inventory.forms.adjustment_amount') }} <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" step="1"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity') }}" placeholder="যেমন: -৫ অথবা ১০" required>
                        <div class="form-text">{{ __('inventory.forms.adjustment_hint') }}</div>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="reason" class="form-label">{{ __('inventory.forms.reason') }} <span class="text-danger">*</span></label>
                        <textarea id="reason" name="reason" rows="2" required
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="যেমন: গুদামে ৫টি পণ্য কম পাওয়া গেছে।">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-check-lg me-1"></i>{{ __('inventory.actions.save_adjustment') }}
                    </button>
                    <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-outline-secondary">বাতিল করুন</a>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
