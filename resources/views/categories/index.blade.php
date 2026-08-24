<x-layouts.app title="ক্যাটাগরিসমূহ">
    <div class="container py-4">
        <h1 class="h3 mb-4">ক্যাটাগরিসমূহ</h1>

        @if ($categories->isEmpty())
            <div class="alert alert-info">কোনো ক্যাটাগরি পাওয়া যায়নি।</div>
        @else
            <div class="row g-4">
                @foreach ($categories as $category)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-category-card :category="$category" />
                    </div>
                    @foreach ($category->children as $child)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-category-card :category="$child" />
                        </div>
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
