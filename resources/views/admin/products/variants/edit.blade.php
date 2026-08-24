<x-layouts.app title="ভ্যারিয়েন্ট সম্পাদনা">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">ভ্যারিয়েন্ট সম্পাদনা করুন</h1>
                <p class="text-muted mb-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-success text-decoration-none">{{ $product->name }}</a> — ভ্যারিয়েন্ট তথ্য আপডেট করুন।
                </p>
            </div>
            <a href="{{ route('admin.products.variants.index', $product) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> ভ্যারিয়েন্ট তালিকায় ফিরে যান
            </a>
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

        <form method="POST" action="{{ route('admin.products.variants.update', [$product, $variant]) }}">
            @csrf
            @method('PUT')
            @include('admin.products.variants._form')

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">আপডেট করুন</button>
                <a href="{{ route('admin.products.variants.index', $product) }}" class="btn btn-outline-secondary">বাতিল করুন</a>
            </div>
        </form>
    </div>
</x-layouts.app>
