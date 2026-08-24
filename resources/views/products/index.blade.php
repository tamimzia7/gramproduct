<x-layouts.app :title="$title ?? 'পণ্যসমূহ'">
    <div class="container py-4">
        <h1 class="h3 mb-4">পণ্যসমূহ</h1>

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title h6 mb-3">অনুসন্ধান</h5>
                        <form method="GET" action="{{ route('products.index') }}">
                            <div class="input-group mb-3">
                                <input type="text" name="search" class="form-control" placeholder="পণ্য খুঁজুন..."
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-success"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <h5 class="card-title h6 mb-3">বিভাগ</h5>
                        <div class="list-group list-group-flush">
                            <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ ! request('category_id') ? 'active' : '' }}">
                                সব পণ্য
                            </a>
                            @foreach ($categories as $category)
                                <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                                   class="list-group-item list-group-item-action {{ request('category_id') == $category->id ? 'active' : '' }}">
                                    {{ $category->name }}
                                </a>
                                @foreach ($category->children as $child)
                                    <a href="{{ route('products.index', ['category_id' => $child->id]) }}"
                                       class="list-group-item list-group-item-action ps-4 {{ request('category_id') == $child->id ? 'active' : '' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                @if ($products->isEmpty())
                    <div class="alert alert-light text-center py-5">
                        <i class="bi bi-box-seam fs-1 text-muted"></i>
                        <p class="mt-3 text-muted mb-0">কোনো পণ্য পাওয়া যায়নি।</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($products as $product)
                            <div class="col-sm-6 col-lg-4">
                                <x-product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
