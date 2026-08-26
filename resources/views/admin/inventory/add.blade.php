<x-admin.layout title="{{ __('inventory.forms.add_title') }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('inventory.forms.add_title') }}</h1>
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
            <form method="POST" action="{{ route('admin.inventory.add', $inventory) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="product_display" class="form-label">{{ __('inventory.forms.current_stock') }}</label>
                        <input type="text" id="product_display" class="form-control" disabled
                               value="{{ \App\Support\BengaliNumber::format($inventory->quantity) }}">
                        <div class="form-text">{{ __('inventory.fields.product') }}: {{ $inventory->variant?->product?->name }} | {{ __('inventory.fields.variant') }}: {{ $inventory->variant?->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label">{{ __('inventory.forms.add_amount') }} <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" min="1" step="1"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity', 10) }}" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="note" class="form-label">{{ __('inventory.forms.note') }}</label>
                        <textarea id="note" name="note" rows="2"
                                  class="form-control @error('note') is-invalid @enderror"
                                  placeholder="যেমন: সরবরাহকারীর নতুন চালান">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-plus-lg me-1"></i>{{ __('inventory.forms.save') }}
                    </button>
                    <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-outline-secondary">বাতিল করুন</a>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
