@props([
    'review',
])

{{-- Component নিজে কোনো কুয়েরি চালায় না — সব ডেটা props থেকে আসে।
     রিভিউ শুধুই বাস্তব (অ্যাপ্রুভড) ডেটা; নাম/রেটিং/টেক্সট সব dynamic,
     এবং সমস্ত ইউজার-টেক্সট Blade-এর default escaping-এর মাধ্যমে নিরাপদ। --}}

@php
    $name = (string) data_get($review, 'name', '');
    $body = (string) data_get($review, 'body', data_get($review, 'review', ''));
    $rating = data_get($review, 'rating');
    $rating = is_numeric($rating) ? (float) $rating : null;
    $productName = data_get($review, 'product_name');
    $verified = (bool) data_get($review, 'verified', false);
    $initials = (string) data_get($review, 'initials', '');

    if ($initials === '' && $name !== '') {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        $initials = implode('', array_map(
            fn (string $word) => mb_substr($word, 0, 1),
            array_slice($words, 0, 2),
        ));
    }
@endphp

<div class="card testimonial-card h-100">
    <div class="card-body d-flex flex-column p-4">
        <div class="testimonial-card__rating mb-3"
             aria-label="{{ $rating !== null ? 'রেটিং '.\App\Support\BengaliNumber::format($rating).' এর মধ্যে ৫' : 'রেটিং নেই' }}">
            @if ($rating !== null)
                @for ($i = 1; $i <= 5; $i++)
                    @if ($rating >= $i)
                        <i class="bi bi-star-fill" aria-hidden="true"></i>
                    @elseif ($rating >= $i - 0.5)
                        <i class="bi bi-star-half" aria-hidden="true"></i>
                    @else
                        <i class="bi bi-star" aria-hidden="true"></i>
                    @endif
                @endfor
                <span class="testimonial-card__rating-value">{{ \App\Support\BengaliNumber::format($rating) }}</span>
            @else
                <i class="bi bi-star text-warning"></i>
            @endif
        </div>

        <p class="testimonial-card__text my-3">{{ $body }}</p>

        <div class="testimonial-card__author d-flex align-items-center gap-2 mt-auto pt-3 border-top">
            <span class="testimonial-card__avatar d-inline-flex align-items-center justify-content-center rounded-circle"
                  aria-hidden="true">{{ $initials }}</span>
            <div class="testimonial-card__meta">
                <strong class="testimonial-card__name d-block">{{ $name }}</strong>
                @if ($productName)
                    <span class="testimonial-card__product d-block small text-muted">
                        {{ __('home.testimonials.product_label') }}: {{ $productName }}
                    </span>
                @endif
            </div>
            @if ($verified)
                <span class="testimonial-card__verified ms-auto badge text-bg-success">
                    <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                    {{ __('home.testimonials.verified') }}
                </span>
            @endif
        </div>
    </div>
</div>