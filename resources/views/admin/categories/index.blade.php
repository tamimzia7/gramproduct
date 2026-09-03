<x-admin.layout title="ক্যাটাগরি পরিচালনা">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">ক্যাটাগরি</h1>
            <p class="text-muted mb-0">পণ্যের ক্যাটাগরি এবং হায়ারার্কি পরিচালনা করুন</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>নতুন ক্যাটাগরি
        </a>
    </div>

    {{-- সফলতা / ত্রুটির বার্তা --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ফিল্টার --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.categories.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label small text-muted">নাম দিয়ে অনুসন্ধান</label>
                        <input type="text" id="search" name="search" class="form-control"
                               placeholder="ক্যাটাগরির নাম..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label small text-muted">স্ট্যাটাস</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">সব স্ট্যাটাস</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="parent_id" class="form-label small text-muted">প্যারেন্ট ক্যাটাগরি</label>
                        <select id="parent_id" name="parent_id" class="form-select">
                            <option value="">সব ক্যাটাগরি</option>
                            <option value="0" {{ request('parent_id') === '0' ? 'selected' : '' }}>— মূল ক্যাটাগরি —</option>
                            @foreach ($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="bi bi-search me-1"></i>অনুসন্ধান
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ক্যাটাগরি টেবিল --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($categories->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-tags fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">কোনো ক্যাটাগরি পাওয়া যায়নি।</p>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>প্রথম ক্যাটাগরি তৈরি করুন
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>নাম</th>
                                <th>প্যারেন্ট</th>
                                <th class="text-center">স্ট্যাটাস</th>
                                <th class="text-center">ফিচার্ড</th>
                                <th class="text-center">ক্রম</th>
                                <th class="text-center">সাব-ক্যাটাগরি</th>
                                <th class="text-center">পণ্য সংখ্যা</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($category->image)
                                                <img src="{{ Storage::url($category->image) }}"
                                                     alt="{{ $category->name }}"
                                                     class="rounded me-2"
                                                     style="width:36px;height:36px;object-fit:cover;">
                                            @else
                                                <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center me-2"
                                                     style="width:36px;height:36px;">
                                                    <i class="bi bi-tag text-success small"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('admin.categories.show', $category) }}"
                                                   class="text-decoration-none fw-semibold">
                                                    {{ $category->name }}
                                                </a>
                                                @if ($category->hasParent())
                                                    <br><small class="text-muted">{{ $category->getHierarchyPath() }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($category->parent)
                                            <a href="{{ route('admin.categories.show', $category->parent) }}"
                                               class="text-decoration-none">
                                                {{ $category->parent->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($category->is_active)
                                            <span class="badge text-bg-success">সক্রিয়</span>
                                        @else
                                            <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($category->is_featured)
                                            <span class="badge text-bg-warning text-dark">
                                                <i class="bi bi-star-fill me-1"></i>ফিচার্ড
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $category->sort_order }}</td>
                                    <td class="text-center">
                                        {{ $category->children_count ?? $category->children()->count() }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-light border">
                                            {{ \App\Support\BengaliNumber::format($category->products_count) }} টি
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.categories.show', $category) }}"
                                               class="btn btn-outline-secondary" title="দেখুন">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category) }}"
                                               class="btn btn-outline-primary" title="সম্পাদনা">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if ($category->trashed())
                                                <form method="POST" action="{{ route('admin.categories.restore', $category->id) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-success" title="পুনরুদ্ধার"
                                                            onclick="return confirm('এই ক্যাটাগরিটি পুনরুদ্ধার করবেন?')">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন"
                                                            onclick="return confirm('এই ক্যাটাগরিটি মুছে ফেলবেন? এই কাজটি আর ফেরানো যাবে না।')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
