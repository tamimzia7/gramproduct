@props([
    'icon',
    'title',
    'description',
])

<div class="card trust-feature-card h-100" {{ $attributes }}>
    <div class="card-body d-flex flex-column align-items-center text-center p-4">
        <span class="trust-feature-card__icon d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
              aria-hidden="true">
            <i class="bi {{ $icon }}"></i>
        </span>
        <h3 class="h6 fw-bold mb-1">{{ $title }}</h3>
        <p class="text-muted small mb-0">{{ $description }}</p>
    </div>
</div>