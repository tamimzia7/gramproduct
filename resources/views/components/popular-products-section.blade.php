@props(['products'])

{{-- জনপ্রিয় পণ্য — স্বয়ংক্রিয়, ডেটাবেস-চালিত (বাস্তব ক্রয়-পরিমাণ থেকে);
     যথেষ্ট ক্রয় ডেটা না থাকলে home.blade-এ পাঠানোই হয় না। --}}
<section class="popular-products-section py-5">
    <div class="container">
        <x-section-header
                :title="__('home.popular.title')"
                :subtitle="__('home.popular.subtitle')"
                :view-all-url="route('products.index')"
                :view-all-text="__('home.popular.view_all')" />

        <x-product-grid :products="$products" :cols="4" />
    </div>
</section>