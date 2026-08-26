@php
    /** @var \App\Models\Address|null $address */
    $address = $address ?? null;
    $divisions = config('location.divisions');
    $selectedDivision = old('division', $address?->division);
    $action = $action ?? route('addresses.store');
    $method = $method ?? 'POST';
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}name">নাম <span class="text-danger">*</span></label>
        <input type="text" id="{{ $idPrefix }}name" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $address?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}phone">মোবাইল নম্বর <span class="text-danger">*</span></label>
        <input type="tel" id="{{ $idPrefix }}phone" name="phone" inputmode="numeric"
               placeholder="01XXXXXXXXX"
               class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $address?->phone) }}" required>
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}division">বিভাগ <span class="text-danger">*</span></label>
        <select id="{{ $idPrefix }}division" name="division"
                class="form-select @error('division') is-invalid @enderror js-division-select"
                data-districts="{{ json_encode($divisions) }}" required>
            <option value="">— নির্বাচন করুন —</option>
            @foreach ($divisions as $division => $districts)
                <option value="{{ $division }}" {{ $selectedDivision === $division ? 'selected' : '' }}>
                    {{ $division }}
                </option>
            @endforeach
        </select>
        @error('division')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}district">জেলা <span class="text-danger">*</span></label>
        <select id="{{ $idPrefix }}district" name="district"
                class="form-select @error('district') is-invalid @enderror" required>
            @php
                $oldDistrict = old('district', $address?->district);
            @endphp
            @if ($selectedDivision)
                @foreach ($divisions[$selectedDivision] ?? [] as $district)
                    <option value="{{ $district }}" {{ $oldDistrict === $district ? 'selected' : '' }}>{{ $district }}</option>
                @endforeach
            @else
                <option value="">আগে বিভাগ নির্বাচন করুন</option>
            @endif
        </select>
        @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}upazila">উপজেলা <span class="text-danger">*</span></label>
        <input type="text" id="{{ $idPrefix }}upazila" name="upazila"
               class="form-control @error('upazila') is-invalid @enderror"
               value="{{ old('upazila', $address?->upazila) }}" required>
        @error('upazila')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}area">এলাকা <span class="text-danger">*</span></label>
        <input type="text" id="{{ $idPrefix }}area" name="area"
               class="form-control @error('area') is-invalid @enderror"
               value="{{ old('area', $address?->area) }}" required>
        @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="{{ $idPrefix }}address_line">ঠিকানা <span class="text-danger">*</span></label>
        <textarea id="{{ $idPrefix }}address_line" name="address_line" rows="2" required
                  class="form-control @error('address_line') is-invalid @enderror"
                  placeholder="বাসা/হোল্ডিং, রোড, গলি">{{ old('address_line', $address?->address_line) }}</textarea>
        @error('address_line')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="{{ $idPrefix }}postal_code">পোস্ট কোড</label>
        <input type="text" id="{{ $idPrefix }}postal_code" name="postal_code" inputmode="numeric"
               class="form-control @error('postal_code') is-invalid @enderror"
               value="{{ old('postal_code', $address?->postal_code) }}">
        @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label" for="{{ $idPrefix }}delivery_note">ডেলিভারি নির্দেশনা</label>
        <input type="text" id="{{ $idPrefix }}delivery_note" name="delivery_note"
               class="form-control @error('delivery_note') is-invalid @enderror"
               placeholder="যেমন: বিকেলের পর ডেলিভারি দিলে সুবিধা হবে।"
               value="{{ old('delivery_note', $address?->delivery_note) }}">
        @error('delivery_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" id="{{ $idPrefix }}is_default" name="is_default" value="1"
                   class="form-check-input"
                   {{ old('is_default', $address?->is_default ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $idPrefix }}is_default">ডিফল্ট ঠিকানা হিসেবে সংরক্ষণ করুন</label>
        </div>
    </div>
</div>
