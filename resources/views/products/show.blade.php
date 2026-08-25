<x-layouts.app :title="$title" :metaDescription="$metaDescription ?? null">
    <div class="container py-4">
        @if ($breadcrumb->isNotEmpty())
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">হোম</a></li>
                    @foreach ($breadcrumb as $crumb)
                        @if ($loop->last)
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb->name }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('categories.show', $crumb->slug) }}">{{ $crumb->name }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="row g-4">
            <div class="col-md-5">
                @if ($product->image)
                    <img src="{{ Storage::url($product->image) }}" class="img-fluid rounded shadow-sm" alt="{{ $product->name }}">
                @else
                    <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center" style="height:400px;">
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <h1 class="h3 mb-2">{{ $product->name }}</h1>

                @if ($product->category)
                    <p class="text-muted mb-3">
                        <a href="{{ route('categories.show', $product->category->slug) }}" class="text-success text-decoration-none">
                            {{ $product->category->name }}
                        </a>
                    </p>
                @endif

                <div class="mb-3">
                    @if ($product->is_featured)
                        <span class="badge text-bg-warning text-dark">বৈশিষ্ট্যযুক্ত</span>
                    @endif
                    @if ($product->is_bestseller)
                        <span class="badge text-bg-danger">বেস্টসেলার</span>
                    @endif
                    @if ($product->is_new_arrival)
                        <span class="badge text-bg-info">নতুন আগমন</span>
                    @endif
                </div>

                <div class="mb-4">
                    @if ($product->variants->isNotEmpty())
                        @php
                            $activeVariants = $product->variants;
                            $minPrice = $activeVariants->min(fn ($v) => $v->hasDiscount() ? $v->discount_price : $v->price);
                            $maxPrice = $activeVariants->max(fn ($v) => $v->hasDiscount() ? $v->discount_price : $v->price);
                        @endphp
                        <span class="fs-3 fw-bold text-success">৳{{ number_format($minPrice, 2) }}</span>
                        @if ($minPrice != $maxPrice)
                            <span class="text-muted fs-5"> — ৳{{ number_format($maxPrice, 2) }}</span>
                        @endif
                    @else
                        @if ($product->hasDiscount())
                            <span class="text-decoration-line-through text-muted fs-5">৳{{ number_format($product->base_price, 2) }}</span>
                            <span class="text-danger fs-3 fw-bold ms-2">৳{{ number_format($product->discount_price, 2) }}</span>
                        @else
                            <span class="fs-3 fw-bold text-success">৳{{ number_format($product->base_price, 2) }}</span>
                        @endif
                        @if ($product->unit)
                            <span class="text-muted ms-1">/ {{ $product->unit }}</span>
                        @endif
                    @endif
                </div>

                @if ($product->variants->isNotEmpty())
                    <div class="mb-4">
                        <h5 class="h6 mb-3">প্যাকেজ নির্বাচন করুন</h5>
                        <div class="list-group">
                            @foreach ($product->variants as $variant)
                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <input type="radio" name="selected_variant" value="{{ $variant->id }}"
                                               class="form-check-input me-2" {{ $loop->first ? 'checked' : '' }}>
                                        <span class="fw-semibold">{{ $variant->name }}</span>
                                        @if ($variant->weight)
                                            <small class="text-muted ms-1">({{ number_format($variant->weight, 2) }} {{ $variant->unit }})</small>
                                        @endif
                                    </div>
                                    <div>
                                        @if ($variant->hasDiscount())
                                            <span class="text-decoration-line-through text-muted small">৳{{ number_format($variant->price, 2) }}</span>
                                            <span class="text-danger fw-semibold ms-1">৳{{ number_format($variant->discount_price, 2) }}</span>
                                        @else
                                            <span class="fw-semibold text-success">৳{{ number_format($variant->price, 2) }}</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product->short_description)
                    <p class="lead">{{ $product->short_description }}</p>
                @endif

                @if ($product->sku)
                    <p class="small text-muted">SKU: {{ $product->sku }}</p>
                @endif

                @if ($product->product_type)
                    <p class="small text-muted">ধরন: {{ $product->product_type }}</p>
                @endif

                @if ($product->origin || $product->farmer_name || $product->seasonal_info)
                    <div class="mb-3 p-3 bg-light rounded">
                        @if ($product->origin)
                            <p class="mb-1 small"><strong>উৎস/এলাকা:</strong> {{ $product->origin }}</p>
                        @endif
                        @if ($product->farmer_name)
                            <p class="mb-1 small"><strong>কৃষক/খামার:</strong> {{ $product->farmer_name }}</p>
                        @endif
                        @if ($product->seasonal_info)
                            <p class="mb-0 small"><strong>মৌসুম:</strong> {{ $product->seasonal_info }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($product->description)
            <div class="mt-5">
                <h2 class="h4 mb-3">বিস্তারিত বিবরণ</h2>
                <div class="content">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>
        @endif

        @if ($relatedProducts->isNotEmpty())
            <div class="mt-5">
                <h2 class="h4 mb-3">সম্পর্কিত পণ্য</h2>
                <div class="row g-4">
                    @foreach ($relatedProducts as $related)
                        <div class="col-sm-6 col-lg-3">
                            <x-product-card :product="$related" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
