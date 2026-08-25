<x-layouts.app title="পাওয়া যায়নি">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="display-1 text-muted mb-3">🔍</div>
                <h1 class="h3 mb-3">পেজটি খুঁজে পাওয়া যায়নি।</h1>
                <p class="text-muted mb-4">আপনি যে পেজটি খুঁজছেন সেটি হয়তো সরিয়ে ফেলা হয়েছে বা ঠিকানাটি ভুল।</p>
                <a href="{{ route('home') }}" class="btn btn-success">হোমে ফিরে যান</a>
            </div>
        </div>
    </div>
</x-layouts.app>
