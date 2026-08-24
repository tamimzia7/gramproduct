<x-layouts.app :title="$title" :metaDescription="$metaDescription ?? null">
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">হোম</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="row align-items-center mb-4">
            <div class="col-md-4">
                @if ($category->image)
                    <img src="{{ Storage::url($category->image) }}" class="img-fluid rounded shadow-sm" alt="{{ $category->name }}">
                @else
                    <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center" style="height:250px;">
                        <i class="bi bi-tags fs-1 text-success"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <h1 class="h3">{{ $category->name }}</h1>
                <div class="mb-3"><x-category-status :category="$category" /></div>
                @if ($category->description)
                    <p class="text-muted">{{ $category->description }}</p>
                @endif
            </div>
        </div>

        @if ($category->children->isNotEmpty())
            <h2 class="h5 mt-4 mb-3">সাব-ক্যাটাগরি</h2>
            <div class="row g-4">
                @foreach ($category->children as $child)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-category-card :category="$child" />
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light mt-4">
                এই ক্যাটাগরির অধীনে এখনও কোনো পণ্য বা সাব-ক্যাটাগরি যোগ করা হয়নি।
            </div>
        @endif
    </div>
</x-layouts.app>
