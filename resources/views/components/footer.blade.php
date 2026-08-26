<footer class="app-footer bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            {{-- ব্র্যান্ড --}}
            <div class="col-12 col-md-6 col-lg-4">
                <h5 class="fw-bold d-flex align-items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="{{ config('app.name', 'Gram Product') }} লোগো"
                         style="height: 32px; width: auto;"
                         loading="lazy">
                    {{ config('app.name', 'Gram Product') }}
                </h5>
                <p class="small text-secondary mb-0">
                    গ্রাম, মাঠ ও নদী থেকে সরাসরি আপনার ঘরে। খাঁটি গ্রামীণ খাদ্য ও কৃষিপণ্য।
                </p>
            </div>

            {{-- নেভিগেশন --}}
            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="text-uppercase small text-secondary">নেভিগেশন</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="{{ route('home') }}" class="link-light text-decoration-none d-inline-block py-1">হোম</a></li>
                    <li><a href="{{ route('products.index') }}" class="link-light text-decoration-none d-inline-block py-1">পণ্যসমূহ</a></li>
                    <li><a href="{{ route('categories.index') }}" class="link-light text-decoration-none d-inline-block py-1">ক্যাটাগরি</a></li>
                    <li><span class="text-secondary d-inline-block py-1">আমাদের সম্পর্কে</span></li>
                    <li><span class="text-secondary d-inline-block py-1">যোগাযোগ</span></li>
                </ul>
            </div>

            {{-- ক্যাটাগরি (ডায়নামিক) --}}
            <div class="col-6 col-md-3 col-lg-3">
                <h6 class="text-uppercase small text-secondary">ক্যাটাগরি</h6>
                @if ($footerCategories->isNotEmpty())
                    <ul class="list-unstyled small mb-0">
                        @foreach ($footerCategories as $category)
                            <li>
                                <a href="{{ route('categories.show', ['category' => $category['slug']]) }}"
                                   class="link-light text-decoration-none d-inline-block py-1">
                                    {{ $category['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="small text-secondary mb-0">শীঘ্রই আসছে।</p>
                @endif
            </div>

            {{-- যোগাযোগ ও নীতিমালা --}}
            <div class="col-12 col-md-12 col-lg-3">
                <h6 class="text-uppercase small text-secondary">সহায়তা</h6>
                <ul class="list-unstyled small mb-2">
                    <li><span class="text-secondary d-inline-block py-1">গোপনীয়তা নীতি</span></li>
                    <li><span class="text-secondary d-inline-block py-1">শর্তাবলি</span></li>
                    <li><span class="text-secondary d-inline-block py-1">রিটার্ন নীতি</span></li>
                </ul>
                <p class="small text-secondary mb-0">
                    <i class="bi bi-envelope me-1"></i>সহযোগিতা: contact@example.com
                </p>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="text-center">
            <p class="small text-secondary mb-0">
                &copy; {{ date('Y') }} {{ config('app.name', 'Gram Product') }}। সর্বস্বত্ব সংরক্ষিত।
            </p>
        </div>
    </div>
</footer>
