<x-layouts.app title="সব ক্যাটাগরি">
    <section class="py-4">
        <div class="container">
            <x-breadcrumb :items="[
                ['label' => __('product.common.home'), 'url' => route('home')],
                ['label' => __('product.common.categories')],
            ]" />

            <div class="mb-4 mt-2">
                <h1 class="h3 mb-1">ক্যাটাগরি</h1>
                <p class="text-muted mb-0">আপনার প্রয়োজনীয় গ্রামীণ পণ্য বেছে নিন</p>
            </div>

            @if ($categories->isEmpty())
                <p class="text-muted">{{ __('home.empty.no_categories') }}</p>
            @else
                <div class="row g-4">
                    @foreach ($categories as $category)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('categories.show', $category) }}" class="text-decoration-none h-100 d-block">
                                <div class="card h-100 border-0 shadow-sm text-center category-card">
                                    <div class="card-body py-4">
                                        @if ($category->image)
                                            <img src="{{ asset('storage/'.$category->image) }}"
                                                 alt="{{ $category->name }}" loading="lazy"
                                                 class="rounded-circle mb-2 object-fit-cover"
                                                 style="width: 72px; height: 72px;">
                                        @else
                                            <div class="fs-1 mb-2" aria-hidden="true">🧺</div>
                                        @endif
                                        <h2 class="h6 mb-1 text-body">{{ $category->name }}</h2>
                                        @if ($category->description)
                                            <p class="small text-muted mb-1">{{ \Illuminate\Support\Str::limit($category->description, 60) }}</p>
                                        @endif
                                        <small class="text-success fw-semibold">
                                            {{ \App\Support\BengaliNumber::format($category->products_count) }} টি পণ্য
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
