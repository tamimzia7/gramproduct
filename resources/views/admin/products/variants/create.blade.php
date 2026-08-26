<x-admin.layout title="{{ __('product.variant.new_title') }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('product.variant.new_title') }}</h1>
            <p class="text-muted mb-0">{{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>ফিরে যান
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

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.variants.store', $product) }}">
                @csrf

                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @include('admin.products.variants._form', ['variant' => null])

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-lg me-1"></i>সংরক্ষণ করুন
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-secondary">বাতিল করুন</a>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
