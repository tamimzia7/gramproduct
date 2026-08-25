<x-admin.layout title="ক্যাটাগরি তৈরি করুন">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">ক্যাটাগরি তৈরি করুন</h1>
            <p class="text-muted mb-0">নতুন পণ্যের ক্যাটাগরি যোগ করুন</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>ফিরে যান
        </a>
    </div>

    {{-- ভ্যালিডেশন ত্রুটি --}}
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
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        {{-- নাম --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required autofocus
                                   placeholder="যেমন: চাল, মাছ, সবজি">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- প্যারেন্ট ক্যাটাগরি --}}
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">প্যারেন্ট ক্যাটাগরি</label>
                            <select id="parent_id" name="parent_id"
                                    class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">— নেই (মূল ক্যাটাগরি) —</option>
                                @foreach ($parentCategories as $cat)
                                    <option value="{{ $cat['id'] }}"
                                            {{ old('parent_id') == $cat['id'] ? 'selected' : '' }}
                                            {{ $cat['disabled'] ? 'disabled' : '' }}>
                                        {{ $cat['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">মূল ক্যাটাগরি তৈরি করতে খালি রাখুন।</div>
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}"
                                   placeholder="খালি রাখলে নাম থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- বিবরণ --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">বিবরণ</label>
                            <textarea id="description" name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3"
                                      placeholder="ঐচ্ছিক ক্যাটাগরি বিবরণ">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ছবি --}}
                        <div class="mb-3">
                            <label for="image" class="form-label">ক্যাটাগরির ছবি</label>
                            <input type="file" id="image" name="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">JPG, PNG অথবা WebP। সর্বোচ্চ 2MB।</div>
                        </div>

                        {{-- ক্রম --}}
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">প্রদর্শনের ক্রম</label>
                            <input type="number" id="sort_order" name="sort_order"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', 0) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">ছোট সংখ্যা আগে প্রদর্শিত হবে।</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        {{-- স্ট্যাটাস --}}
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="card-title">স্ট্যাটাস</h6>
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" id="is_active" name="is_active" value="1"
                                           class="form-check-input"
                                           {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">সক্রিয়</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_featured" value="0">
                                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                           class="form-check-input"
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">ফিচার্ড</label>
                                </div>
                            </div>
                        </div>

                        {{-- SEO --}}
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="card-title">SEO</h6>
                                <div class="mb-3">
                                    <label for="seo_title" class="form-label small">SEO শিরোনাম</label>
                                    <input type="text" id="seo_title" name="seo_title"
                                           class="form-control form-control-sm @error('seo_title') is-invalid @enderror"
                                           value="{{ old('seo_title') }}"
                                           placeholder="খালি রাখলে ক্যাটাগরির নাম ব্যবহৃত হবে">
                                    @error('seo_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label for="seo_description" class="form-label small">SEO বিবরণ</label>
                                    <textarea id="seo_description" name="seo_description"
                                              class="form-control form-control-sm @error('seo_description') is-invalid @enderror"
                                              rows="3"
                                              placeholder="খালি রাখলে ক্যাটাগরির বিবরণ ব্যবহৃত হবে">{{ old('seo_description') }}</textarea>
                                    @error('seo_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">বাতিল</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>ক্যাটাগরি তৈরি করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
