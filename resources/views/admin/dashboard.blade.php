<x-layouts.app title="অ্যাডমিন ড্যাশবোর্ড">
    <div class="container py-5">
        <h1 class="h3 mb-4">অ্যাডমিন ড্যাশবোর্ড</h1>

        <div class="alert alert-info">
            অ্যাডমিন ভিত্তি তৈরি হয়েছে। ভূমিকা ও অনুমতি ব্যবস্থাপনা এবং মডিউল পরিচালনা
            অ্যাডমিন মডিউল পর্যায়ে তৈরি করা হবে।
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">লগইন করেছেন</h2>
                        <p class="mb-0">{{ auth()->user()->name }}</p>
                        <p class="text-muted small mb-0">{{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
