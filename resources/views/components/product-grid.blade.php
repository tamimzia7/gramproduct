@props(['products', 'cols' => 4])

@php
    // ব্রেকপয়েন্ট ক্লাস — cols=4 (ডিফল্ট) → col-lg-3
    $lgClass = match ((int) $cols) {
        2 => 'col-lg-6',
        3 => 'col-lg-4',
        default => 'col-lg-3',
    };
@endphp

@if ($products->isNotEmpty())
    <div class="row g-4" {{ $attributes }}>
        @foreach ($products as $product)
            <div class="col-6 col-md-4 {{ $lgClass }}">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
@endif
