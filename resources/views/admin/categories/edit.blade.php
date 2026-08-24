<x-layouts.app title="ক্যাটাগরি সম্পাদনা">
    <div class="container py-4">
        <h1 class="h3 mb-4">ক্যাটাগরি সম্পাদনা করুন</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.categories._form')

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">আপডেট করুন</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
            </div>
        </form>
    </div>
</x-layouts.app>
