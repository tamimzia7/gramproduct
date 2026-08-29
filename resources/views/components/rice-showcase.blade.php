@props([
    'products',
    'categories',
    'rootCategory',
    'title' => 'আমাদের চাল',
    'subtitle' => 'গ্রামের মিল থেকে বাছাই করা মানসম্মত বিভিন্ন ধরনের চাল',
    'viewAllText' => 'সব চাল দেখুন',
    'allFilterText' => 'সব চাল',
    'productCount' => null,
])

{{-- Component নিজে কোনো কুয়েরি চালায় না — সব ডেটা props থেকে আসে (HomepageService) --}}
<section class="rice-showcase pt-4" aria-label="{{ $title }}">
    <div class="container">
        {{-- শিরোনাম — বামে থিম, ডানে "সব চাল দেখুন" (root ক্যাটাগরি পেজে) --}}
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-1 text-success-emphasis">{{ $title }}</h2>
                <p class="text-muted mb-0">
                    {{ $subtitle }}
                    @if ($productCount !== null && $productCount > 0)
                        <span class="text-success fw-semibold">
                            · {{ \App\Support\BengaliNumber::format($productCount) }} ধরনের চাল
                        </span>
                    @endif
                </p>
            </div>
            <a href="{{ route('categories.show', $rootCategory) }}"
               class="text-decoration-none text-success fw-semibold rice-showcase__view-all-lg">
                {{ $viewAllText }} <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        {{-- Quick-link pills — active child ক্যাটাগরিগুলো; mobile-এ horizontal scroll --}}
        @if ($categories->isNotEmpty())
            <nav class="rice-showcase__filters mb-4" aria-label="চালের ধরন">
                <a href="{{ route('categories.show', $rootCategory) }}"
                   class="rice-showcase__pill rice-showcase__pill--active">
                    {{ $allFilterText }}
                </a>
                @foreach ($categories as $child)
                    <a href="{{ route('categories.show', $child) }}"
                       class="rice-showcase__pill">
                        {{ $child->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- পণ্য গ্রিড — mobile 2 / tablet 3–4 / desktop 5–6 --}}
        <x-product-grid :products="$products" :cols="5" />

        {{-- Mobile-এ "সব চাল দেখুন" button --}}
        <div class="text-center mt-4 d-lg-none">
            <a href="{{ route('categories.show', $rootCategory) }}"
               class="btn btn-sm btn-outline-success rounded-pill px-3">
                {{ $viewAllText }}
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>
