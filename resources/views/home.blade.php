<x-layouts.app title="হোম">
    {{-- Hero --}}
    <section class="bg-success-subtle py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold text-success-emphasis">
                        গ্রাম, মাঠ ও নদী থেকে সরাসরি আপনার ঘরে।
                    </h1>
                    <p class="lead mt-3">
                        চাল, মাছ, সবজি, ডাল, মসলা, তেল, মধু ও ফল &mdash;
                        সরাসরি বিশ্বস্ত কৃষক ও খামার থেকে সংগৃহীত।
                    </p>
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('products.index') }}" class="btn btn-success btn-lg">এখনই কিনুন</a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-success btn-lg">ক্যাটাগরি দেখুন</a>
                    </div>
                </div>
                <div class="col-lg-5 text-center mt-4 mt-lg-0">
                    <div class="p-4 bg-white rounded-circle shadow-sm d-inline-flex" style="width: 220px; height: 220px;">
                        <div class="m-auto text-center">
                            <div class="fs-1">🌾</div>
                            <div class="text-success fw-semibold">গ্রামীণ বাণিজ্য</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ফিচার্ড ক্যাটাগরি (ডায়নামিক) --}}
    @if ($featuredCategories->isNotEmpty())
        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0">ক্যাটাগরি অনুযায়ী কিনুন</h2>
                    <a href="{{ route('categories.index') }}" class="text-decoration-none">সব দেখুন →</a>
                </div>
                <div class="row g-4">
                    @foreach ($featuredCategories as $category)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('categories.show', $category) }}" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm text-center">
                                    <div class="card-body py-4">
                                        <div class="fs-1 mb-2">🧺</div>
                                        <h3 class="h6 mt-2 mb-0 text-body">{{ $category->name }}</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- নতুন পণ্য (ডায়নামিক) --}}
    @if ($latestProducts->isNotEmpty())
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0">সদ্য যোগ হওয়া পণ্য</h2>
                    <a href="{{ route('products.index') }}" class="text-decoration-none">সব পণ্য দেখুন →</a>
                </div>
                <div class="row g-4">
                    @foreach ($latestProducts as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- বিশ্বাসযোগ্যতা --}}
    <section class="py-5">
        <div class="container text-center">
            <h2 class="h4">খামার থেকে ঘরে, আস্থার সাথে</h2>
            <p class="text-muted">
                প্রতিটি পণ্যে থাকছে উৎস, কৃষক ও টাটকাভাবের তথ্য &mdash; কারণ খাঁটি খাবারের
                পেছনে স্বচ্ছ গল্প থাকা প্রয়োজন।
            </p>
        </div>
    </section>
</x-layouts.app>
