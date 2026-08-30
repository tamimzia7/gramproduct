@props([
    'number',
    'icon',
    'title',
    'description',
])

{{-- Order step card — props-only (no DB, no lang calls).
     সংখ্যা বাংলা ডিজিটে রেন্ডার হয় (যেমন ১ → ০১)। ----}}

<div class="card order-step h-100 text-center" {{ $attributes }}>
    <div class="order-step__body d-flex flex-column align-items-center p-4">
        <span class="order-step__number d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
              aria-hidden="true">
            {{ \App\Support\BengaliNumber::format(str_pad((string) $number, 2, '0', STR_PAD_LEFT)) }}
        </span>
        <span class="order-step__icon d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
              aria-hidden="true">
            <i class="bi {{ $icon }}"></i>
        </span>
        <h3 class="h6 fw-bold mb-1">{{ $title }}</h3>
        <p class="text-muted small mb-0">{{ $description }}</p>
    </div>
</div>