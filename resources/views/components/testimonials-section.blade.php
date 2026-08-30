@props([
    'reviews',
])

{{-- Component নিজে কোনো কুয়েরি চালায় না — শুধুই props (HomepageService থেকে)।
     হালকা slider: scroll-snap track + আগে/পরের বোতাম (JS-এ behavior),
     কিবোর্ড নেভিগেশন ও prefers-reduced-motion সাপোর্ট। --}}

<section class="testimonials-section py-5" aria-labelledby="testimonials-heading">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
            <div>
                <h2 id="testimonials-heading" class="h4 mb-1">{{ __('home.testimonials.title') }}</h2>
                <p class="text-muted mb-0">{{ __('home.testimonials.subtitle') }}</p>
            </div>
        </div>

        <div class="testimonials-slider position-relative" data-testimonials-slider
             aria-roledescription="carousel"
             aria-label="{{ __('home.testimonials.title') }}">

            <button type="button"
                    class="testimonials-slider__nav start-0 position-absolute top-50 translate-middle-y"
                    data-testimonials-prev
                    aria-label="{{ __('home.testimonials.prev') }}">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>

            <div class="testimonials-slider__track"
                 data-testimonials-track
                 role="list"
                 tabindex="0"
                 aria-label="{{ __('home.testimonials.title') }}">
                @foreach ($reviews as $review)
                    <div class="testimonials-slider__slide" role="listitem">
                        <x-testimonial-card :review="$review" />
                    </div>
                @endforeach
            </div>

            <button type="button"
                    class="testimonials-slider__nav end-0 position-absolute top-50 translate-middle-y"
                    data-testimonials-next
                    aria-label="{{ __('home.testimonials.next') }}">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</section>