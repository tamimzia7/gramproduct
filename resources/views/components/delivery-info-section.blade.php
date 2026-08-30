@props([])

{{-- Delivery Information section — সম্পূর্ণ স্ট্যাটিক lang-ড্রiven (কোনো DB/JS নেই)।
     ডেলিভারি জোন/চার্জ/ট্র্যাকিং মডিউল নেই বলে কোনো ফেক এলাকা/চার্জ/সময় দেখানো হয় না।
     Tracking কার্ডের অনুপস্থিতিতে বাস্তব ক্যাশ অন ডেলিভারি সুবিধা দেখানো হয়। --}}

<section class="delivery-info py-5">
    <div class="container">
        <x-section-header
                :title="__('home.delivery.title')"
                :subtitle="__('home.delivery.subtitle')" />

        <div class="row g-3 g-md-4 row-cols-1 row-cols-md-3">
            @foreach (__('home.delivery.items') as $item)
                <div class="col d-flex">
                    <x-delivery-info-card
                            :icon="$item['icon']"
                            :title="$item['title']"
                            :description="$item['description']" />
                </div>
            @endforeach
        </div>
    </div>
</section>