<x-layouts.app title="অনুমতি নেই">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="display-1 text-danger mb-3">🔒</div>
                <h1 class="h3 mb-3">এই কাজটি করার অনুমতি আপনার নেই।</h1>
                <p class="text-muted mb-4">এই পেজটি দেখার বা কাজটি সম্পন্ন করার জন্য প্রয়োজনীয় অনুমতি আপনার অ্যাকাউন্টে নেই।</p>
                <a href="{{ route('home') }}" class="btn btn-success">হোমে ফিরে যান</a>
            </div>
        </div>
    </div>
</x-layouts.app>
