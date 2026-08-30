@props([
    'icon',
    'title',
    'description',
])

{{-- Delivery info card — props-only (no DB, no lang calls)। --}}

<div class="card delivery-info-card h-100" {{ $attributes }}>
    <div class="card-body d-flex flex-column align-items-center text-center p-4">
        <span class="delivery-info-card__icon d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
              aria-hidden="true">
            <i class="bi {{ $icon }}"></i>
        </span>
        <h3 class="h6 fw-bold mb-1">{{ $title }}</h3>
        <p class="text-muted small mb-0">{{ $description }}</p>
    </div>
</div>