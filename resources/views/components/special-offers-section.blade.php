@props(['products'])

{{-- বিশেষ অফার — সম্পূর্ণ ডেটাবেস-চালিত (সক্রিয় ছাড়যুক্ত পণ্য);
     উপযুক্ত পণ্য না থাকলে home.blade-এ পাঠানোই হয় না। --}}
<section class="special-offers-section py-5">
    <div class="container">
        <x-section-header
                :title="__('home.offers.title')"
                :subtitle="__('home.offers.subtitle')"
                :view-all-url="route('products.index')"
                :view-all-text="__('home.offers.view_all')" />

        <x-product-grid :products="$products" :cols="4" />
    </div>
</section>