@props([])

{{-- How It Works / Order Process section — সম্পূর্ণ স্ট্যাটিক lang-ড্রiven
     (কোনো DB কুয়েরি/JS নেই)। ৪টি ধাপ <x-order-step> দিয়ে রেন্ডার হয়।
     ডেলিভারি সময় বা ফ্রি ডেলিভারির প্রতিশ্রুতি দেওয়া হয় না। ----}}

<section class="order-process py-5">
    <div class="container">
        <x-section-header
                :title="__('home.order.title')"
                :subtitle="__('home.order.subtitle')" />

        <div class="order-process__grid position-relative">
            <div class="order-process__flow" aria-hidden="true"></div>

            <div class="row g-3 g-md-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
                @foreach (__('home.order.steps') as $step)
                    <div class="col d-flex flex-column order-process__col">
                        <x-order-step
                                :number="$step['number']"
                                :icon="$step['icon']"
                                :title="$step['title']"
                                :description="$step['description']" />
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-outline-success px-4 rounded-pill">
                {{ __('home.order.cta') }}
                <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>