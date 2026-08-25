@php
    $product = $product ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="name" class="form-label">পণ্যের নাম <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $product?->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                    <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror"
                           value="{{ old('sku', $product?->sku) }}" required>
                    <div class="form-text">প্রতিটি পণ্যের SKU অনন্য হতে হবে।</div>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="slug" class="form-label">স্লাগ</label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $product?->slug) }}">
                    <div class="form-text">ফাঁকা রাখলে নাম থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে।</div>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">বিভাগ <span class="text-danger">*</span></label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">বিভাগ নির্বাচন করুন</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $category->id === (string) old('category_id', $product?->category_id))>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="short_description" class="form-label">সংক্ষিপ্ত বিবরণ</label>
            <textarea id="short_description" name="short_description" rows="2" class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $product?->short_description) }}</textarea>
            <div class="form-text">সর্বোচ্চ ৫০০ অক্ষর।</div>
            @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">বিস্তারিত বিবরণ</label>
            <textarea id="description" name="description" rows="6" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product?->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="seo_title" class="form-label">SEO শিরোনাম</label>
            <input type="text" id="seo_title" name="seo_title" class="form-control @error('seo_title') is-invalid @enderror"
                   value="{{ old('seo_title', $product?->seo_title) }}">
            @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="seo_description" class="form-label">SEO বিবরণ</label>
            <textarea id="seo_description" name="seo_description" rows="2" class="form-control @error('seo_description') is-invalid @enderror">{{ old('seo_description', $product?->seo_description) }}</textarea>
            @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="image" class="form-label">পণ্যের ছবি</label>
            @if ($product?->image)
                <div class="mb-2">
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-height:150px;">
                </div>
            @endif
            <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3">
            <div class="col-6">
                <div class="mb-3">
                    <label for="base_price" class="form-label">মূল মূল্য (৳) <span class="text-danger">*</span></label>
                    <input type="number" id="base_price" name="base_price" step="0.01" min="0"
                           class="form-control @error('base_price') is-invalid @enderror"
                           value="{{ old('base_price', $product?->base_price) }}" required>
                    @error('base_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="discount_price" class="form-label">ছাড়ের মূল্য (৳)</label>
                    <input type="number" id="discount_price" name="discount_price" step="0.01" min="0"
                           class="form-control @error('discount_price') is-invalid @enderror"
                           value="{{ old('discount_price', $product?->discount_price) }}">
                    @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <div class="mb-3">
                    <label for="unit" class="form-label">একক</label>
                    <input type="text" id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror"
                           value="{{ old('unit', $product?->unit ?? 'কেজি') }}">
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="product_type" class="form-label">পণ্যের ধরন</label>
                    <input type="text" id="product_type" name="product_type" class="form-control @error('product_type') is-invalid @enderror"
                           value="{{ old('product_type', $product?->product_type ?? 'সাধারণ') }}">
                    @error('product_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="origin" class="form-label">উৎস/এলাকা</label>
            <input type="text" id="origin" name="origin" class="form-control @error('origin') is-invalid @enderror"
                   value="{{ old('origin', $product?->origin) }}" placeholder="যেমন: বগুড়া, রাজশাহী">
            @error('origin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="farmer_name" class="form-label">কৃষক/খামারের নাম</label>
            <input type="text" id="farmer_name" name="farmer_name" class="form-control @error('farmer_name') is-invalid @enderror"
                   value="{{ old('farmer_name', $product?->farmer_name) }}">
            @error('farmer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="seasonal_info" class="form-label">মৌসুমী তথ্য</label>
            <input type="text" id="seasonal_info" name="seasonal_info" class="form-control @error('seasonal_info') is-invalid @enderror"
                   value="{{ old('seasonal_info', $product?->seasonal_info) }}" placeholder="যেমন: শীতকালীন, সারা বছর">
            @error('seasonal_info')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product?->is_active ?? true))>
            <label for="is_active" class="form-check-label">সক্রিয়</label>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_featured" name="is_featured" value="1" class="form-check-input" @checked(old('is_featured', $product?->is_featured ?? false))>
            <label for="is_featured" class="form-check-label">বৈশিষ্ট্যযুক্ত</label>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1" class="form-check-input" @checked(old('is_bestseller', $product?->is_bestseller ?? false))>
            <label for="is_bestseller" class="form-check-label">বেস্টসেলার</label>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_new_arrival" name="is_new_arrival" value="1" class="form-check-input" @checked(old('is_new_arrival', $product?->is_new_arrival ?? false))>
            <label for="is_new_arrival" class="form-check-label">নতুন আগমন</label>
        </div>
    </div>
</div>
