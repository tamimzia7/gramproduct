@props(['products'])

{{-- নতুন যোগ করা পণ্য — সম্পূর্ণ স্বয়ংক্রিয় (সর্বশেষ active পণ্য, created_at DESC);
     কোনো পণ্য না থাকলে home.blade-এ পাঠানোই হয় না। --}}
<section class="new-arrivals-section py-5">
    <div class="container">
        <x-section-header
                :title="__('home.new_arrivals.title')"
                :subtitle="__('home.new_arrivals.subtitle')"
                :view-all-url="route('products.index')"
                :view-all-text="__('home.new_arrivals.view_all')" />

        <x-product-grid :products="$products" :cols="4" />
    </div>
</section>