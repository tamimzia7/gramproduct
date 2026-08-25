<footer class="app-footer bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5 class="fw-bold">{{ config('app.name', 'Gram Product') }}</h5>
                <p class="small text-secondary mb-0">
                    গ্রাম, মাঠ ও নদী থেকে সরাসরি আপনার ঘরে। খাঁটি গ্রামীণ খাদ্য ও কৃষিপণ্য।
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="small text-secondary mb-0">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Gram Product') }}। সর্বস্বত্ব সংরক্ষিত।
                </p>
            </div>
        </div>
    </div>
</footer>
