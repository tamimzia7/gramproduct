<x-layouts.app title="ইমেইল যাচাই">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h1 class="h4 mb-3">আপনার ইমেইল ঠিকানা যাচাই করুন</h1>
                        <p class="text-muted">
                            রেজিস্টার করার জন্য ধন্যবাদ! শুরু করার আগে, অনুগ্রহ করে আমাদের পাঠানো লিংকে
                            ক্লিক করে আপনার ইমেইল ঠিকানা যাচাই করুন। আপনি এটি পাননি হলে, আমরা আবার পাঠাতে পারি।
                        </p>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <div class="d-grid gap-2 mt-3">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-success">যাচাইকরণ ইমেইল আবার পাঠান</button>
                            </form>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">লগআউট</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
