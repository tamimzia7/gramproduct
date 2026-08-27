@props(['products', 'cols' => 5])

@php
    // Bootstrap row-cols — mobile 2, tablet 3–4, desktop/large per $cols
    $lgClass = match ((int) $cols) {
        3 => 'row-cols-lg-3',
        4 => 'row-cols-lg-4',
        default => 'row-cols-lg-5 row-cols-xl-6',
    };
@endphp

@if ($products->isNotEmpty())
    <div class="row g-3 g-md-4 row-cols-2 row-cols-sm-3 row-cols-md-4 {{ $lgClass }}" {{ $attributes }}>
        @foreach ($products as $product)
            <div class="col d-flex">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
@endif
