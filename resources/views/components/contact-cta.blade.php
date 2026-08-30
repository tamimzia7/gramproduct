@props(['actions' => [], 'contactUrl' => null])

{{-- Quick Contact CTA — "কোনো কিছু জানতে চান?"
     Props-নির্ভর; DB query নেই। অ্যাকশনগুলো বিদ্যমান configuration
     (config/shop.php 'contact', .env-চালিত) থেকে আসে — প্লেসহোল্ডার কখনোই নয়।
     কোনো যোগাযোগ মাধ্যম না থাকলে home.blade-এ পাঠানোই হয় না। --}}
<section class="contact-cta-section py-5">
    <div class="container">
        <div class="text-center">
            <x-section-header
                :title="__('home.contact.title')"
                :subtitle="__('home.contact.subtitle')" />

            <div class="contact-cta-actions d-flex flex-column flex-sm-row justify-content-center align-items-stretch gap-3">
                @if ($contactUrl)
                    <a href="{{ $contactUrl }}"
                       class="btn btn-success btn-lg contact-cta-btn px-4 py-3"
                       aria-label="{{ __('home.contact.primary_aria') }}">
                        {{ __('home.contact.primary') }}
                    </a>
                @endif

                @foreach ($actions as $action)
                    <a href="{{ $action['href'] }}"
                       class="btn btn-outline-success btn-lg contact-cta-btn px-4 py-3 d-inline-flex align-items-center justify-content-center gap-2"
                       @if (!empty($action['external'])) target="_blank" rel="noopener" @endif
                       aria-label="{{ $action['aria'] }}">
                        <i class="bi {{ $action['icon'] }}" aria-hidden="true"></i>
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>