@props([
    'products',
    'categories',
    'rootCategory',
    'title' => 'তাজা মাছ',
    'subtitle' => 'বিল-ঝিল ও গ্রামের জলাশয় থেকে সংগ্রহ করা মাছ',
    'viewAllText' => 'সব মাছ দেখুন',
    'allFilterText' => 'সব মাছ',
    'productCount' => null,
])

{{-- Component নিজে কোনো কুয়েরি চালায় না — সব ডেটা props থেকে আসে (HomepageService) --}}
<section class="fish-showcase pt-4" aria-label="{{ $title }}">
    <div class="container">
        {{-- শিরোনাম — বামে থিম, ডানে "সব মাছ দেখুন" (root ক্যাটাগরি পেজে) --}}
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-1 text-success-emphasis d-flex align-items-center gap-2">
                    {{-- পানির মৃদু ঢেউ — ছোট আলংকারিক উপাদান; পণ্যকে নিচে ঠেলে দেয় না --}}
                    <svg class="fish-showcase__wave" width="22" height="22" viewBox="0 0 24 24" fill="none"
                         aria-hidden="true" focusable="false">
                        <path d="M22 8c-2.1-2.6-4.2-3.9-6.3-3.9-3.2 0-4.5 4.5-7.4 4.5-2.1 0-3.1-1.5-4.3-2.9"
                              stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <path d="M2 16c2.1 2.6 4.2 3.9 6.3 3.9 3.2 0 4.5-4.5 7.4-4.5 2.1 0 3.1 1.5 4.3 2.9"
                              stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    <span>{{ $title }}</span>
                </h2>
                <p class="text-muted mb-0">
                    {{ $subtitle }}
                    @if ($productCount !== null && $productCount > 0)
                        <span class="d-inline-flex align-items-center gap-1 text-success fw-semibold">
                            <span aria-hidden="true">·</span>
                            {{ \App\Support\BengaliNumber::format($productCount) }} ধরনের মাছ
                        </span>
                    @endif
                </p>
            </div>
            <a href="{{ route('categories.show', $rootCategory) }}"
               class="text-decoration-none text-success fw-semibold fish-showcase__view-all-lg">
                {{ $viewAllText }} <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        {{-- Quick-link pills — active child ক্যাটাগরিগুলো; mobile-এ horizontal scroll --}}
        @if ($categories->isNotEmpty())
            <nav class="fish-showcase__filters mb-4" aria-label="মাছের ধরন">
                <a href="{{ route('categories.show', $rootCategory) }}"
                   class="fish-showcase__pill fish-showcase__pill--active">
                    {{ $allFilterText }}
                </a>
                @foreach ($categories as $child)
                    <a href="{{ route('categories.show', $child) }}"
                       class="fish-showcase__pill">
                        {{ $child->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- পণ্য গ্রিড — mobile 2 / tablet 3–4 / desktop 5–6 --}}
        <x-product-grid :products="$products" :cols="5" />

        {{-- Mobile-এ "সব মাছ দেখুন" button --}}
        <div class="text-center mt-4 d-lg-none">
            <a href="{{ route('categories.show', $rootCategory) }}"
               class="btn btn-sm btn-outline-success rounded-pill px-3">
                {{ $viewAllText }}
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>