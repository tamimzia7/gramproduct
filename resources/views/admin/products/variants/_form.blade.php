@php
    $variant = $variant ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('product.variant.name') }} <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $variant?->name) }}"
                   placeholder="{{ __('product.variant.name_placeholder') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="sku" class="form-label">{{ __('product.variant.sku') }} <span class="text-danger">*</span></label>
            <input type="text" id="sku" name="sku"
                   class="form-control @error('sku') is-invalid @enderror"
                   value="{{ old('sku', $variant?->sku) }}" required>
            <div class="form-text">{{ __('product.variant.sku_hint') }}</div>
            @error('sku')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="quantity" class="form-label">{{ __('product.variant.quantity') }} <span class="text-danger">*</span></label>
            <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                   class="form-control @error('quantity') is-invalid @enderror"
                   value="{{ old('quantity', $variant?->quantity ?? 1) }}" required>
            @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="unit" class="form-label">{{ __('product.variant.unit') }} <span class="text-danger">*</span></label>
            <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                <option value="">— নির্বাচন করুন —</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->value }}"
                            {{ old('unit', $variant?->unit?->value) === $unit->value ? 'selected' : '' }}>
                        {{ __('product.units.'.$unit->value) }}
                    </option>
                @endforeach
            </select>
            @error('unit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="sort_order" class="form-label">{{ __('product.variant.sort_order') }}</label>
            <input type="number" id="sort_order" name="sort_order" min="0"
                   class="form-control @error('sort_order') is-invalid @enderror"
                   value="{{ old('sort_order', $variant?->sort_order ?? 0) }}">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="price" class="form-label">{{ __('product.variant.price') }} (৳) <span class="text-danger">*</span></label>
            <input type="number" id="price" name="price" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $variant?->price) }}" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="compare_at_price" class="form-label">{{ __('product.variant.old_price') }} (৳)</label>
            <input type="number" id="compare_at_price" name="compare_at_price" step="0.01" min="0"
                   class="form-control @error('compare_at_price') is-invalid @enderror"
                   value="{{ old('compare_at_price', $variant?->compare_at_price) }}">
            @error('compare_at_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="stock_status" class="form-label">{{ __('product.variant.status') }} <span class="text-danger">*</span></label>
            <select id="stock_status" name="stock_status"
                    class="form-select @error('stock_status') is-invalid @enderror" required>
                @foreach (\App\Enums\StockStatus::cases() as $status)
                    <option value="{{ $status->value }}"
                            {{ old('stock_status', $variant?->stock_status?->value ?? 'in_stock') === $status->value ? 'selected' : '' }}>
                        {{ __('product.stock.'.$status->value) }}
                    </option>
                @endforeach
            </select>
            @error('stock_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   class="form-check-input" {{ old('is_active', $variant ? (bool) $variant->is_active : true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('product.variant.active') }}</label>
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" id="is_default" name="is_default" value="1"
                   class="form-check-input" {{ old('is_default', $variant?->is_default ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">{{ __('product.variant.default') }}</label>
        </div>
    </div>
</div>
