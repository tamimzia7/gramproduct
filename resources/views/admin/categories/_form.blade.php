@php
    $category = $category ?? null;
    $parentValue = old('parent_id', $category?->parent_id);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="name" class="form-label">ক্যাটাগরির নাম <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $category?->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">স্লাগ</label>
            <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
                   value="{{ old('slug', $category?->slug) }}">
            <div class="form-text">ফাঁকা রাখলে নাম থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে। প্রতিটি ক্যাটাগরির স্লাগ অনন্য হতে হবে।</div>
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="parent_id" class="form-label">অভিভাবক ক্যাটাগরি</label>
            <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                <option value="">— প্রধান ক্যাটাগরি (কোনো অভিভাবক নয়) —</option>
                @foreach ($parentOptions ?? [] as $option)
                    <option value="{{ $option->id }}" @selected((string) $option->id === (string) $parentValue)>
                        {{ str_repeat('— ', $option->depth ?? 0) }}{{ $option->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">বিবরণ</label>
            <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category?->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="seo_title" class="form-label">SEO শিরোনাম</label>
            <input type="text" id="seo_title" name="seo_title" class="form-control @error('seo_title') is-invalid @enderror"
                   value="{{ old('seo_title', $category?->seo_title) }}">
            @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="seo_description" class="form-label">SEO বিবরণ</label>
            <textarea id="seo_description" name="seo_description" rows="2" class="form-control @error('seo_description') is-invalid @enderror">{{ old('seo_description', $category?->seo_description) }}</textarea>
            @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="image" class="form-label">ক্যাটাগরি ছবি</label>
            @if ($category?->image)
                <div class="mb-2">
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-height:120px;">
                </div>
            @endif
            <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="sort_order" class="form-label">সাজানোর ক্রম</label>
            <input type="number" id="sort_order" name="sort_order" min="0"
                   class="form-control @error('sort_order') is-invalid @enderror"
                   value="{{ old('sort_order', $category?->sort_order ?? 0) }}">
            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $category?->is_active ?? true))>
            <label for="is_active" class="form-check-label">সক্রিয়</label>
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" id="is_featured" name="is_featured" value="1" class="form-check-input" @checked(old('is_featured', $category?->is_featured ?? false))>
            <label for="is_featured" class="form-check-label">বৈশিষ্ট্যযুক্ত</label>
        </div>
    </div>
</div>
