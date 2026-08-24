<x-layouts.app title="ক্যাটাগরি ব্যবস্থাপনা">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">ক্যাটাগরি ব্যবস্থাপনা</h1>
                <p class="text-muted mb-0">পণ্যের ক্যাটাগরি ও সাব-ক্যাটাগরি পরিচালনা করুন।</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> নতুন ক্যাটাগরি যোগ করুন
            </a>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>নাম</th>
                                <th>স্লাগ</th>
                                <th>অভিভাবক</th>
                                <th>স্ট্যাটাস</th>
                                <th>সাজানো</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>
                                        {{ str_repeat('— ', $category->depth ?? 0) }}{{ $category->name }}
                                        @if ($category->is_featured)
                                            <i class="bi bi-star-fill text-warning ms-1" title="বৈশিষ্ট্যযুক্ত"></i>
                                        @endif
                                    </td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td>{{ $category->parent?->name ?? '—' }}</td>
                                    <td><x-category-status :category="$category" /></td>
                                    <td>{{ $category->sort_order }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> সম্পাদনা
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি এই ক্যাটাগরিটি মুছতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> মুছুন
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">কোনো ক্যাটাগরি পাওয়া যায়নি।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
