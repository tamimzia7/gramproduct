@props([
    'title',
    'subtitle' => null,
    'viewAllUrl' => null,
    'viewAllText' => 'সব দেখুন',
])

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h2 class="h4 mb-{{ $subtitle ? '1' : '0' }}">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($viewAllUrl)
        <a href="{{ $viewAllUrl }}" class="text-decoration-none text-success fw-semibold">
            {{ $viewAllText }} <i class="bi bi-arrow-right"></i>
        </a>
    @endif
</div>
