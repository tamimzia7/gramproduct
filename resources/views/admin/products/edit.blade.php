<x-admin.layout title="পণ্য সম্পাদনা করুন">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">পণ্য সম্পাদনা করুন</h1>
            <p class="text-muted mb-0">{{ $product->name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info me-1">
                <i class="bi bi-eye me-1"></i>দেখুন
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>ফিরে যান
            </a>
        </div>
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

    {{-- বিদ্যমান ছবি ব্যবস্থাপনা --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">ছবিসমূহ ({{ $product->images->count() }})</h2>
        </div>
        <div class="card-body">
            @if ($product->images->isEmpty())
                <p class="text-muted mb-0">এই পণ্যের কোনো ছবি নেই। নিচের ফর্ম থেকে ছবি যোগ করুন।</p>
            @else
                <div class="row g-3">
                    @foreach ($product->images as $image)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="border rounded p-2 h-100">
                                <img src="{{ $image->url() }}"
                                     alt="{{ $image->alt_text ?? $product->name }}"
                                     class="w-100 rounded"
                                     style="height:140px;object-fit:cover;">
                                @if ($image->is_primary)
                                    <span class="badge text-bg-success mt-2">প্রধান ছবি</span>
                                @endif
                                <div class="mt-2 d-flex gap-1">
                                    @unless ($image->is_primary)
                                        <form method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}"
                                              class="flex-grow-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                                প্রধান ছবি করুন
                                            </button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}"
                                          onsubmit="return confirm('আপনি কি এই ছবিটি মুছে ফেলতে চান?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="ছবি মুছে ফেলুন">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
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
                                       value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" id="sku" name="sku"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       value="{{ old('sku', $product->sku) }}">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="slug" class="form-label">স্লাগ</label>
                                <input type="text" id="slug" name="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug', $product->slug) }}">
                                <div class="form-text">অপরিবর্তিত রাখলে URL একই থাকবে।</div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label">ক্যাটাগরি <span class="text-danger">*</span></label>
                                <select id="category_id" name="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">— ক্যাটাগরি নির্বাচন করুন —</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
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
                                        <option value="{{ $unit->value }}"
                                                {{ old('unit', $product->unit?->value) === $unit->value ? 'selected' : '' }}>
                                            {{ __('product.units.' . $unit->value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="product_type" class="form-label">পণ্যের ধরন</label>
                                <input type="text" id="product_type" name="product_type"
                                       class="form-control @error('product_type') is-invalid @enderror"
                                       value="{{ old('product_type', $product->product_type ?? 'physical') }}">
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
                                      class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $product->short_description) }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="description" class="form-label">বিস্তারিত বিবরণ</label>
                            <textarea id="description" name="description" rows="5"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- নতুন ছবি যোগ --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">নতুন ছবি যোগ করুন</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">প্রধান ছবি (হিসেবে সেট হবে)</label>
                                <input type="file" id="image" name="image"
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
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
                                       value="{{ old('seo_title', $product->seo_title) }}">
                                @error('seo_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="seo_description" class="form-label">SEO বিবরণ</label>
                                <textarea id="seo_description" name="seo_description" rows="2"
                                          class="form-control @error('seo_description') is-invalid @enderror">{{ old('seo_description', $product->seo_description) }}</textarea>
                                @error('seo_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                   value="{{ old('base_price', $product->base_price) }}" required>
                            @error('base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="compare_at_price" class="form-label">আগের মূল্য (৳)</label>
                            <input type="number" step="0.01" min="0" id="compare_at_price" name="compare_at_price"
                                   class="form-control @error('compare_at_price') is-invalid @enderror"
                                   value="{{ old('compare_at_price', $product->compare_at_price) }}">
                            @error('compare_at_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="discount_price" class="form-label">বিক্রয় মূল্য (৳, ঐচ্ছিক)</label>
                            <input type="number" step="0.01" min="0" id="discount_price" name="discount_price"
                                   class="form-control @error('discount_price') is-invalid @enderror"
                                   value="{{ old('discount_price', $product->discount_price) }}">
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
                                <option value="in_stock" {{ old('stock_status', $product->stock_status?->value) === 'in_stock' ? 'selected' : '' }}>
                                    {{ __('product.stock.in_stock') }}
                                </option>
                                <option value="out_of_stock" {{ old('stock_status', $product->stock_status?->value) === 'out_of_stock' ? 'selected' : '' }}>
                                    {{ __('product.stock.out_of_stock') }}
                                </option>
                            </select>
                            @error('stock_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   class="form-check-input" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">সক্রিয়</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                   class="form-check-input" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">বিশেষ পণ্য</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_bestseller" value="0">
                            <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1"
                                   class="form-check-input" {{ old('is_bestseller', $product->is_bestseller) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_bestseller">সর্বাধিক বিক্রিত</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_new_arrival" value="0">
                            <input type="checkbox" id="is_new_arrival" name="is_new_arrival" value="1"
                                   class="form-check-input" {{ old('is_new_arrival', $product->is_new_arrival) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_new_arrival">নতুন</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="is_seasonal" value="0">
                            <input type="checkbox" id="is_seasonal" name="is_seasonal" value="1"
                                   class="form-check-input" {{ old('is_seasonal', $product->is_seasonal) ? 'checked' : '' }}>
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
                                   value="{{ old('origin', $product->origin) }}">
                            @error('origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="farmer_name" class="form-label">কৃষকের নাম</label>
                            <input type="text" id="farmer_name" name="farmer_name"
                                   class="form-control @error('farmer_name') is-invalid @enderror"
                                   value="{{ old('farmer_name', $product->farmer_name) }}">
                            @error('farmer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="seasonal_info" class="form-label">মৌসুমি তথ্য</label>
                            <input type="text" id="seasonal_info" name="seasonal_info"
                                   class="form-control @error('seasonal_info') is-invalid @enderror"
                                   value="{{ old('seasonal_info', $product->seasonal_info) }}">
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
                                   value="{{ old('sort_order', $product->sort_order) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>আপডেট করুন
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
                        </div>
                    </div>
                </div>

                {{-- ডিলিট --}}
                @can('delete', $product)
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                          onsubmit="return confirm('আপনি কি এই পণ্যটি মুছে ফেলতে চান?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i>পণ্য মুছে ফেলুন
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </form>
</x-admin.layout>
