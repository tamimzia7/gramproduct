<x-layouts.app title="হোম">
    <section class="bg-success-subtle py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold text-success-emphasis">
                        গ্রাম, মাঠ ও জল থেকে আপনার ঘরে।
                    </h1>
                    <p class="lead mt-3">
                        প্রামাণ্য গ্রাম-ভিত্তিক খাদ্য ও কৃষি পণ্য &mdash; চাল, মাছ, সবজি,
                        বীজ, মধু ও আরও অনেক কিছু &mdash; সরাসরি কৃষক ও খামার থেকে সংগ্রহ করা।
                    </p>
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('categories.index') }}" class="btn btn-success btn-lg">এখনই কিনুন</a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-success btn-lg">পণ্য দেখুন</a>
                    </div>
                </div>
                <div class="col-lg-5 text-center mt-4 mt-lg-0">
                    <div class="p-4 bg-white rounded-circle shadow-sm d-inline-flex" style="width: 220px; height: 220px;">
                        <div class="m-auto text-center">
                            <div class="fs-1">🌾</div>
                            <div class="text-success fw-semibold">গ্রাম বাণিজ্য</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
        $featuredCategories = \App\Models\Category::active()
            ->whereNull('parent_id')
            ->featured()
            ->ordered()
            ->limit(8)
            ->get();
    @endphp

    @if ($featuredCategories->isNotEmpty())
        <section class="py-5">
            <div class="container">
                <h2 class="h4 mb-4">বৈশিষ্ট্যযুক্ত ক্যাটাগরি</h2>
                <div class="row g-4">
                    @foreach ($featuredCategories as $category)
                        <div class="col-6 col-md-3">
                            <x-category-card :category="$category" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="h4">খামার থেকে ঘরে, বিশ্বাসের সাথে</h2>
            <p class="text-muted">
                প্রতিটি পণ্যের সাথে রয়েছে উৎস, কৃষক ও সতেজতার তথ্য &mdash; কারণ প্রামাণ্য খাদ্যের
                জন্য স্বচ্ছতা প্রয়োজন।
            </p>
        </div>
    </section>
</x-layouts.app>
