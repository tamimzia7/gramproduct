@props(['category'])

@php
    // Component নিজে কুয়েরি চালায় না — সব ডেটা props থেকে আসে
@endphp

<a href="{{ route('categories.show', $category) }}"
   class="category-card text-decoration-none"
   aria-label="{{ $category->name }} ক্যাটাগরি">
    <div class="category-card__inner">
        <div class="category-card__media">
            @if ($category->image)
                <img src="{{ asset('storage/'.$category->image) }}"
                     alt="{{ $category->name }} ক্যাটাগরি"
                     class="category-card__img"
                     loading="lazy" decoding="async"
                     width="64" height="64">
            @else
                <span class="category-card__fallback" aria-hidden="true">🧺</span>
            @endif
        </div>

        <h3 class="category-card__name">{{ $category->name }}</h3>

        @if (($category->products_count ?? 0) > 0)
            <small class="category-card__count">
                {{ __('home.quick_categories.products_count', ['count' => \App\Support\BengaliNumber::format($category->products_count)]) }}
            </small>
        @endif
    </div>
</a>
