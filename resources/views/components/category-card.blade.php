@props(['category'])

<div class="card h-100 border-0 shadow-sm">
    <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none">
        @if ($category->image)
            <img src="{{ Storage::url($category->image) }}" class="card-img-top"
                 alt="{{ $category->name }}" style="height:170px;object-fit:cover;">
        @else
            <div class="bg-success-subtle d-flex align-items-center justify-content-center" style="height:170px;">
                <i class="bi bi-tags fs-1 text-success"></i>
            </div>
        @endif
    </a>
    <div class="card-body text-center">
        <h3 class="h6 card-title mb-0">
            <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none text-dark">
                {{ $category->name }}
            </a>
        </h3>
        @if ($category->description)
            <p class="small text-muted mt-2 mb-0">{{ Str::limit($category->description, 80) }}</p>
        @endif
    </div>
</div>
