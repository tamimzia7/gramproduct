<x-layouts.app title="মজুদ যোগ করুন">
    <div class="container py-4">
        <h1 class="h3 mb-4">মজুদ যোগ করুন</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.inventories.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">পণ্য <span class="text-danger">*</span></label>
                        <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">পণ্য নির্বাচন করুন</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="product_variant_id" class="form-label">ভ্যারিয়েন্ট</label>
                        <select id="product_variant_id" name="product_variant_id" class="form-select @error('product_variant_id') is-invalid @enderror">
                            <option value="">— কোনো ভ্যারিয়েন্ট নয় —</option>
                        </select>
                        @error('product_variant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">পণ্য নির্বাচন করলে ভ্যারিয়েন্ট দেখা যাবে।</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">মজুদের পরিমাণ <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" min="1"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity') }}" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="reason" class="form-label">কারণ</label>
                        <input type="text" id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror"
                               value="{{ old('reason') }}" placeholder="যেমন: নতুন ব্যাচ এসেছে">
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">মজুদ যোগ করুন</button>
                <a href="{{ route('admin.inventories.index') }}" class="btn btn-outline-secondary">বাতিল করুন</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('product_id').addEventListener('change', function() {
            const productId = this.value;
            const variantSelect = document.getElementById('product_variant_id');

            if (!productId) {
                variantSelect.innerHTML = '<option value="">— কোনো ভ্যারিয়েন্ট নয় —</option>';
                return;
            }

            fetch(`/admin/products/${productId}/variants`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">— কোনো ভ্যারিয়েন্ট নয় —</option>';
                    data.forEach(variant => {
                        options += `<option value="${variant.id}">${variant.name} (${variant.weight} ${variant.unit})</option>`;
                    });
                    variantSelect.innerHTML = options;
                })
                .catch(() => {
                    variantSelect.innerHTML = '<option value="">— কোনো ভ্যারিয়েন্ট নয় —</option>';
                });
        });
    </script>
</x-layouts.app>
