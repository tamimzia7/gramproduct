@php
    $variant = $variant ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="name" class="form-label">ভ্যারিয়েন্টের নাম <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $variant?->name) }}" placeholder="যেমন: ১ কেজি, ৫ কেজি, ৫০০ গ্রাম" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                    <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror"
                           value="{{ old('sku', $variant?->sku) }}" required>
                    <div class="form-text">প্রতিটি ভ্যারিয়েন্টের SKU অনন্য হতে হবে।</div>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="weight" class="form-label">ওজন/পরিমাণ</label>
                    <input type="number" id="weight" name="weight" step="0.01" min="0"
                           class="form-control @error('weight') is-invalid @enderror"
                           value="{{ old('weight', $variant?->weight) }}">
                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="unit" class="form-label">একক</label>
                    <input type="text" id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror"
                           value="{{ old('unit', $variant?->unit ?? 'কেজি') }}">
                    <div class="form-text">যেমন: কেজি, গ্রাম, লিটার, মিলিলিটার, পিস।</div>
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="minimum_order" class="form-label">সর্বনিম্ন অর্ডার</label>
                    <input type="number" id="minimum_order" name="minimum_order" min="1"
                           class="form-control @error('minimum_order') is-invalid @enderror"
                           value="{{ old('minimum_order', $variant?->minimum_order ?? 1) }}">
                    @error('minimum_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="price" class="form-label">মূল্য (৳) <span class="text-danger">*</span></label>
            <input type="number" id="price" name="price" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $variant?->price) }}" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="discount_price" class="form-label">ছাড়ের মূল্য (৳)</label>
            <input type="number" id="discount_price" name="discount_price" step="0.01" min="0"
                   class="form-control @error('discount_price') is-invalid @enderror"
                   value="{{ old('discount_price', $variant?->discount_price) }}">
            @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="maximum_order" class="form-label">সর্বোচ্চ অর্ডার</label>
            <input type="number" id="maximum_order" name="maximum_order" min="1"
                   class="form-control @error('maximum_order') is-invalid @enderror"
                   value="{{ old('maximum_order', $variant?->maximum_order) }}">
            <div class="form-text">ফাঁকা রাখলে কোনো সীমা থাকবে না।</div>
            @error('maximum_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $variant?->is_active ?? true))>
            <label for="is_active" class="form-check-label">সক্রিয়</label>
        </div>
    </div>
</div>
