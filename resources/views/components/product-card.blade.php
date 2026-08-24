@props(['product'])

<div class="card h-100 border-0 shadow-sm">
    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none">
        @if ($product->image)
            <img src="{{ Storage::url($product->image) }}" class="card-img-top"
                 alt="{{ $product->name }}" style="height:180px;object-fit:cover;">
        @else
            <div class="bg-success-subtle d-flex align-items-center justify-content-center" style="height:180px;">
                <i class="bi bi-box-seam fs-1 text-success"></i>
            </div>
        @endif
    </a>
    <div class="card-body d-flex flex-column">
        <div class="mb-1">
            @if ($product->is_featured)
                <span class="badge text-bg-warning text-dark" style="font-size:0.65rem;">বৈশিষ্ট্যযুক্ত</span>
            @endif
            @if ($product->is_bestseller)
                <span class="badge text-bg-danger" style="font-size:0.65rem;">বেস্টসেলার</span>
            @endif
            @if ($product->is_new_arrival)
                <span class="badge text-bg-info" style="font-size:0.65rem;">নতুন</span>
            @endif
        </div>
        <h3 class="h6 card-title mb-1">
            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                {{ $product->name }}
            </a>
        </h3>
        @if ($product->category)
            <p class="small text-muted mb-2">{{ $product->category->name }}</p>
        @endif
        <div class="mt-auto">
            @if ($product->variants->isNotEmpty())
                @php
                    $minPrice = $product->variants->min(fn ($v) => $v->hasDiscount() ? $v->discount_price : $v->price);
                    $maxPrice = $product->variants->max(fn ($v) => $v->hasDiscount() ? $v->discount_price : $v->price);
                @endphp
                <span class="text-success fw-semibold">৳{{ number_format($minPrice, 2) }}</span>
                @if ($minPrice != $maxPrice)
                    <span class="text-muted small"> — ৳{{ number_format($maxPrice, 2) }}</span>
                @endif
            @else
                @if ($product->hasDiscount())
                    <span class="text-decoration-line-through text-muted small">৳{{ number_format($product->base_price, 2) }}</span>
                    <span class="text-danger fw-semibold">৳{{ number_format($product->discount_price, 2) }}</span>
                @else
                    <span class="text-success fw-semibold">৳{{ number_format($product->base_price, 2) }}</span>
                @endif
                @if ($product->unit)
                    <span class="text-muted small">/{{ $product->unit }}</span>
                @endif
            @endif
        </div>
    </div>
</div>
