@props(['products'])

{{-- এ সময়ের পণ্য — সম্পূর্ণ ডেটাবেস-চালিত (অ্যাডমিন-চিহ্নিত is_seasonal পণ্য);
     কোনো মৌসুমি পণ্য না থাকলে home.blade-এ পাঠানোই হয় না। --}}
<section class="seasonal-products-showcase py-5">
    <div class="container">
        <x-section-header
                :title="__('home.seasonal.title')"
                :subtitle="__('home.seasonal.subtitle')"
                :view-all-url="route('products.index')"
                :view-all-text="__('home.seasonal.view_all')" />

        <x-product-grid :products="$products" :cols="4" />
    </div>
</section>