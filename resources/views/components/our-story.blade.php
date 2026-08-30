@props([
    'title',
    'subtitle',
    'description',
    'imageAlt',
    'ctaUrl',
    'ctaLabel',
    'productsUrl',
    'productsLabel',
])

<section class="our-story py-5" aria-labelledby="our-story-heading">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-12 col-lg-6">
                <div class="our-story__visual"
                     role="img"
                     aria-label="{{ $imageAlt }}">
                    <svg viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
                        <title>গ্রামের প্রাকৃতিক দৃশ্য</title>
                        <defs>
                            <linearGradient id="our-story-sky" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#fdf3e0" />
                                <stop offset="0.55" stop-color="#f6f0dc" />
                                <stop offset="1" stop-color="#e8f0dd" />
                            </linearGradient>
                            <linearGradient id="our-story-field" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#cfe3bf" />
                                <stop offset="1" stop-color="#9cc47f" />
                            </linearGradient>
                            <linearGradient id="our-story-water" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#aed6d6" />
                                <stop offset="1" stop-color="#8fc4c9" />
                            </linearGradient>
                        </defs>

                        <rect width="800" height="600" fill="url(#our-story-sky)" />

                        <circle cx="620" cy="120" r="42" fill="#f7d98b" opacity="0.9" />

                        <path d="M0 420 Q200 380 400 410 T800 400 V600 H0 Z" fill="url(#our-story-field)" />
                        <path d="M0 470 Q200 440 400 465 T800 455 V600 H0 Z" fill="#8db878" opacity="0.7" />

                        <path d="M0 470 Q200 440 400 465 T800 455 L800 520 Q600 500 400 515 T0 520 Z"
                              fill="url(#our-story-water)" />
                        <ellipse cx="150" cy="492" rx="90" ry="5" fill="#ffffff" opacity="0.18" />
                        <ellipse cx="620" cy="500" rx="120" ry="6" fill="#ffffff" opacity="0.14" />

                        <path d="M330 375 C330 300 360 260 405 205" fill="none" stroke="#5f8f4f"
                              stroke-width="10" stroke-linecap="round" />
                        <path d="M405 205 C400 175 395 160 402 140" fill="none" stroke="#5f8f4f"
                              stroke-width="8" stroke-linecap="round" />
                        <circle cx="402" cy="138" r="10" fill="#8db878" />

                        <path d="M338 365 Q350 340 365 328" fill="none" stroke="#5f8f4f"
                              stroke-width="5" stroke-linecap="round" />
                        <path d="M392 200 Q405 185 425 178" fill="none" stroke="#5f8f4f"
                              stroke-width="5" stroke-linecap="round" />
                    </svg>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="our-story__content">
                    <h2 id="our-story-heading" class="our-story__title h3 fw-bold mb-2">{{ $title }}</h2>
                    <p class="our-story__subtitle text-muted mb-3">{{ $subtitle }}</p>
                    <p class="our-story__description mb-4">{{ $description }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $ctaUrl }}" class="btn btn-success px-4">{{ $ctaLabel }}</a>
                        <a href="{{ $productsUrl }}" class="btn btn-outline-success px-4">{{ $productsLabel }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>