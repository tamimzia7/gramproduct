<x-admin.layout title="আমার প্রোফাইল">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">আমার প্রোফাইল</h1>
            <p class="text-muted mb-0">আপনার অ্যাকাউন্ট তথ্য ও পাসওয়ার্ড আপডেট করুন</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <div class="admin-avatar admin-avatar--xl mb-3">{{ mb_substr($user->name, 0, 1) }}</div>
                    <h5 class="mb-0">{{ $user->name }}</h5>
                    <p class="text-muted small mb-0">{{ $user->email }}</p>
                    @if ($user->roles->isNotEmpty())
                        <div class="mt-2">
                            @foreach ($user->roles as $role)
                                <span class="badge bg-success">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    <p class="text-muted small mt-3 mb-0">সদস্য হয়েছেন: {{ $user->created_at?->format('d M, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">নাম <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">ইমেইল <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">পাসওয়ার্ড পরিবর্তন</h6>
                        <div class="form-text mb-3 text-muted">পাসওয়ার্ড পরিবর্তন করতে চাইলে নিচের ঘরগুলো পূরণ করুন, অন্যথায় খালি রাখুন।</div>

                        <div class="mb-3">
                            <label for="password" class="form-label">নতুন পাসওয়ার্ড</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" autocomplete="new-password">
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>প্রোফাইল সংরক্ষণ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
