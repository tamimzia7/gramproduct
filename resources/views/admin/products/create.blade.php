<x-layouts.app title="নতুন পণ্য">
    <div class="container py-4">
        <h1 class="h3 mb-4">নতুন পণ্য যোগ করুন</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.products._form')

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">সংরক্ষণ করুন</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
            </div>
        </form>
    </div>
</x-layouts.app>
