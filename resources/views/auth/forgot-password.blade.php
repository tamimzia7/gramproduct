<x-layouts.app title="পাসওয়ার্ড রিসেট">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3 text-center">পাসওয়ার্ড ভুলে গেছেন?</h1>
                        <p class="text-muted small mb-4">
                            আপনার ইমেইল ঠিকানা দিন, আমরা পাসওয়ার্ড রিসেট লিংক পাঠিয়ে দেব।
                        </p>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">ইমেইল ঠিকানা</label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success w-100">রিসেট লিংক পাঠান</button>

                            <div class="text-center mt-3">
                                <a href="{{ route('login') }}" class="small">লগইনে ফিরে যান</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
