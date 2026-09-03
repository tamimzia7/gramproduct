<x-admin.layout title="নতুন পণ্য যোগ করুন">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">নতুন পণ্য যোগ করুন</h1>
            <p class="text-muted mb-0">একটি নতুন পণ্যের তথ্য দিন</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
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

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            {{-- বাম কলাম --}}
            <div class="col-lg-8">
                {{-- মৌলিক তথ্য --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">মৌলিক তথ্য</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">পণ্যের নাম <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required autofocus
                                       placeholder="যেমন: নাজিরশাইল চাল">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="name_bn" class="form-label">পণ্যের বাংলা নাম</label>
                                <input type="text" id="name_bn" name="name_bn"
                                       class="form-control @error('name_bn') is-invalid @enderror"
                                       value="{{ old('name_bn') }}"
                                       placeholder="যেমন: নাজিরশাইল চাল">
                                @error('name_bn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" id="sku" name="sku"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       value="{{ old('sku') }}"
                                       placeholder="RICE-NS-001">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="slug" class="form-label">স্লাগ</label>
                                <input type="text" id="slug" name="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}"
                                       placeholder="nazirshail-chal">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">খালি রাখলে SKU/নাম থেকে তৈরি হবে।</div>
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label">ক্যাটাগরি <span class="text-danger">*</span></label>
                                <select id="category_id" name="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">— ক্যাটাগরি নির্বাচন করুন —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->display_name ?? $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="unit" class="form-label">একক</label>
                                <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror">
                                    <option value="">— নির্বাচন করুন —</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->value }}" {{ old('unit') === $unit->value ? 'selected' : '' }}>
                                            {{ __('product.units.' . $unit->value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="weight" class="form-label">ওজন (কেজি)</label>
                                <input type="number" step="0.01" min="0" id="weight" name="weight"
                                       class="form-control @error('weight') is-invalid @enderror"
                                       value="{{ old('weight') }}">
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="brand" class="form-label">ব্র্যান্ড</label>
                                <input type="text" id="brand" name="brand"
                                       class="form-control @error('brand') is-invalid @enderror"
                                       value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tags" class="form-label">ট্যাগ</label>
                                <input type="text" id="tags" name="tags"
                                       class="form-control @error('tags') is-invalid @enderror"
                                       value="{{ old('tags') }}"
                                       placeholder="কমা দিয়ে আলাদা করুন: চাল, জৈব, গ্রামীণ">
                                @error('tags')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">কমা দিয়ে একাধিক ট্যাগ দিন।</div>
                            </div>

                            <div class="col-md-3">
                                <label for="product_type" class="form-label">পণ্যের ধরন</label>
                                <input type="text" id="product_type" name="product_type"
                                       class="form-control @error('product_type') is-invalid @enderror"
                                       value="{{ old('product_type', 'physical') }}">
                                @error('product_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- বিবরণ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">বিবরণ</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="short_description" class="form-label">সংক্ষিপ্ত বিবরণ</label>
                            <textarea id="short_description" name="short_description" rows="2"
                                      class="form-control @error('short_description') is-invalid @enderror"
                                      placeholder="পণ্যটি সম্পর্কে এক-দুই লাইন">{{ old('short_description') }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="description" class="form-label">বিস্তারিত বিবরণ</label>
                            <textarea id="description" name="description" rows="5"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="গ্রামের মিল থেকে সংগ্রহ করা উন্নত মানের...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ছবি --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">ছবি</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">প্রধান ছবি</label>
                                <input type="file" id="image" name="image"
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">JPG, PNG বা WebP। সর্বোচ্চ 2MB।</div>
                            </div>
                            <div class="col-md-6">
                                <label for="image_alt_text" class="form-label">ছবির Alt টেক্সট (বাংলা)</label>
                                <input type="text" id="image_alt_text" name="image_alt_text"
                                       class="form-control"
                                       value="{{ old('image_alt_text') }}"
                                       placeholder="যেমন: নাজিরশাইল চালের প্যাকেট">
                            </div>
                            <div class="col-12">
                                <label for="images" class="form-label">অতিরিক্ত ছবি (সর্বোচ্চ ৫টি)</label>
                                <input type="file" id="images" name="images[]" multiple
                                       class="form-control @error('images.*') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp">
                                @error('images.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">SEO</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seo_title" class="form-label">SEO শিরোনাম</label>
                                <input type="text" id="seo_title" name="seo_title"
                                       class="form-control @error('seo_title') is-invalid @enderror"
                                       value="{{ old('seo_title') }}"
                                       placeholder="খালি রাখলে পণ্যের নাম ব্যবহৃত হবে">
                                @error('seo_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="seo_description" class="form-label">SEO বিবরণ</label>
                                <textarea id="seo_description" name="seo_description" rows="2"
                                          class="form-control @error('seo_description') is-invalid @enderror"
                                          placeholder="খালি রাখলে সংক্ষিপ্ত বিবরণ ব্যবহৃত হবে">{{ old('seo_description') }}</textarea>
                                @error('seo_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ডান কলাম --}}
            <div class="col-lg-4">
                {{-- মূল্য --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">মূল্য</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="base_price" class="form-label">মূল্য (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" id="base_price" name="base_price"
                                   class="form-control @error('base_price') is-invalid @enderror"
                                   value="{{ old('base_price') }}" required>
                            @error('base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="compare_at_price" class="form-label">আগের মূল্য (৳)</label>
                            <input type="number" step="0.01" min="0" id="compare_at_price" name="compare_at_price"
                                   class="form-control @error('compare_at_price') is-invalid @enderror"
                                   value="{{ old('compare_at_price') }}">
                            <div class="form-text">বড় হলে ক্রস-আউট ও ছাড়ের % দেখাবে।</div>
                            @error('compare_at_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="discount_price" class="form-label">বিক্রয় মূল্য (৳, ঐচ্ছিক)</label>
                            <input type="number" step="0.01" min="0" id="discount_price" name="discount_price"
                                   class="form-control @error('discount_price') is-invalid @enderror"
                                   value="{{ old('discount_price') }}">
                            <div class="form-text">দিলে কার্টে এটিই প্রযোজ্য হবে।</div>
                            @error('discount_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- স্ট্যাটাস ও ফ্ল্যাগ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">স্ট্যাটাস</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="stock_status" class="form-label">স্টক স্ট্যাটাস</label>
                            <select id="stock_status" name="stock_status"
                                    class="form-select @error('stock_status') is-invalid @enderror">
                                <option value="in_stock" {{ old('stock_status', 'in_stock') === 'in_stock' ? 'selected' : '' }}>
                                    {{ __('product.stock.in_stock') }}
                                </option>
                                <option value="out_of_stock" {{ old('stock_status') === 'out_of_stock' ? 'selected' : '' }}>
                                    {{ __('product.stock.out_of_stock') }}
                                </option>
                            </select>
                            @error('stock_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="low_stock_threshold" class="form-label">মজুদের সতর্কতা সীমা</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold"
                                   min="0"
                                   class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                   value="{{ old('low_stock_threshold', 5) }}">
                            @error('low_stock_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">এই সংখ্যার কম স্টক থাকলে সতর্কতা দেখাবে।</div>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   class="form-check-input" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">সক্রিয়</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                   class="form-check-input" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">বিশেষ পণ্য</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_bestseller" value="0">
                            <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1"
                                   class="form-check-input" {{ old('is_bestseller') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_bestseller">সর্বাধিক বিক্রিত</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_new_arrival" value="0">
                            <input type="checkbox" id="is_new_arrival" name="is_new_arrival" value="1"
                                   class="form-check-input" {{ old('is_new_arrival', '1') === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_new_arrival">নতুন</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="is_seasonal" value="0">
                            <input type="checkbox" id="is_seasonal" name="is_seasonal" value="1"
                                   class="form-check-input" {{ old('is_seasonal') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_seasonal">মৌসুমি</label>
                        </div>
                    </div>
                </div>

                {{-- উৎস তথ্য --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">উৎস তথ্য</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="origin" class="form-label">উৎস</label>
                            <input type="text" id="origin" name="origin"
                                   class="form-control @error('origin') is-invalid @enderror"
                                   value="{{ old('origin') }}" placeholder="যেমন: সিরাজগঞ্জ">
                            @error('origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="farmer_name" class="form-label">কৃষকের নাম</label>
                            <input type="text" id="farmer_name" name="farmer_name"
                                   class="form-control @error('farmer_name') is-invalid @enderror"
                                   value="{{ old('farmer_name') }}">
                            @error('farmer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="seasonal_info" class="form-label">মৌসুমি তথ্য</label>
                            <input type="text" id="seasonal_info" name="seasonal_info"
                                   class="form-control @error('seasonal_info') is-invalid @enderror"
                                   value="{{ old('seasonal_info') }}">
                            @error('seasonal_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ক্রম + অ্যাকশন --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">প্রদর্শনের ক্রম</label>
                            <input type="number" id="sort_order" name="sort_order" min="0"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>সংরক্ষণ করুন
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-admin.layout>
