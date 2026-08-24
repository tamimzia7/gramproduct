<x-layouts.app title="পণ্য সম্পাদনা">
    <div class="container py-4">
        <h1 class="h3 mb-4">পণ্য সম্পাদনা করুন</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.products._form')

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">আপডেট করুন</button>
                <a href="{{ route('admin.products.variants.index', $product) }}" class="btn btn-outline-success">
                    <i class="bi bi-list-ul"></i> ভ্যারিয়েন্ট ব্যবস্থাপনা
                    @if ($product->variants()->count() > 0)
                        <span class="badge text-bg-success ms-1">{{ $product->variants()->count() }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
            </div>
        </form>
    </div>
</x-layouts.app>
