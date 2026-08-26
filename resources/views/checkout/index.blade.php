<x-layouts.app :title="__('checkout.title')">
    <section class="py-4">
        <div class="container">
            <x-breadcrumb :items="[
                ['label' => __('product.common.home'), 'url' => route('home')],
                ['label' => __('cart.cart.title'), 'url' => route('cart.index')],
                ['label' => __('checkout.title')],
            ]" />

            <h1 class="h3 mt-2 mb-4">
                <i class="bi bi-credit-card-2-front me-2"></i>{{ __('checkout.title') }}
            </h1>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button>
                </div>
            @endif

            @if ($errors->has('checkout') || session('issues'))
                <div class="alert alert-warning">
                    <p class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first('checkout') ?? __('checkout.errors.cart_changed') }}
                    </p>
                    @if (($issues ?? session('issues')) !== [])
                        <ul class="mb-2 small">
                            @foreach ($issues ?? session('issues') as $issue)
                                <li>{{ $issue['message'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-success btn-sm">
                        {{ __('cart.cart.title') }} দেখুন
                    </a>
                </div>
            @endif

            {{-- মূল্য-পরিবর্তন নোটিশ (non-blocking) --}}
            @if (($notices ?? []) !== [])
                <div class="alert alert-info">
                    @foreach ($notices as $notice)
                        <div><i class="bi bi-info-circle me-1"></i>{{ $notice }}</div>
                    @endforeach
                </div>
            @endif

            @if ($isEmptyState || (! $summary && $error !== null && str_contains($error, 'খালি')))
                {{-- খালি কার্ট — চেকআউট ফর্ম দেখানো হয় না --}}
                <div class="text-center py-5">
                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                    <p class="h5 mt-3">{{ __('checkout.errors.empty_cart') }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success mt-2">পণ্য দেখুন</a>
                </div>
            @elseif (! $summary)
                {{-- অবৈধ কার্ট — confirm অসম্ভব, warning + কার্ট লিংক উপরে দেখানো হয়েছে --}}
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                    <p class="h6 mt-3 mb-3">{{ __('checkout.errors.cart_changed') }}</p>
                    <a href="{{ route('cart.index') }}" class="btn btn-success">{{ __('cart.cart.title') }} দেখুন</a>
                </div>
            @else
                <form method="POST" action="{{ route('checkout.store') }}">
                    @csrf

                    <div class="row g-4">
                        {{-- ================= বাম: ঠিকানা + ডেলিভারি ================= --}}
                        <div class="col-lg-7">
                            {{-- ডেলিভারি ঠিকানা --}}
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h2 class="h6 mb-0"><i class="bi bi-geo-alt me-1"></i>{{ __('checkout.address_section') }}</h2>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="collapse" data-bs-target="#new-address-form"
                                            aria-expanded="false">
                                        <i class="bi bi-plus-lg me-1"></i>{{ __('checkout.new_address') }}
                                    </button>
                                </div>
                                <div class="card-body">
                                    @if ($addresses->isEmpty())
                                        <p class="text-muted small">
                                            কোনো ঠিকানা সংরক্ষিত নেই। নিচে নতুন ঠিকানা যোগ করুন।
                                        </p>
                                    @endif

                                    {{-- সংরক্ষিত ঠিকানাগুলো --}}
                                    <div class="vstack gap-2">
                                        @foreach ($addresses as $address)
                                            <label class="d-block border rounded-3 p-3 position-relative address-option
                                                          {{ old('address_id', $defaultAddressId) == $address->id ? 'border-success bg-success-subtle' : '' }}"
                                                   style="cursor: pointer;">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div class="form-check mb-0">
                                                        <input type="radio" name="address_id" value="{{ $address->id }}"
                                                               id="address-{{ $address->id }}"
                                                               class="form-check-input mt-1"
                                                               {{ old('address_id', $defaultAddressId) == $address->id ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="flex-grow-1 small">
                                                        <span class="fw-semibold">{{ $address->name }}</span>
                                                        <span class="text-muted ms-1">{{ \App\Support\BengaliNumber::format($address->phone) }}</span>
                                                        @if ($address->is_default)
                                                            <span class="badge text-bg-success ms-1">{{ __('checkout.default_badge') }}</span>
                                                        @endif
                                                        <div class="text-muted">
                                                            {{ $address->address_line }}, {{ $address->area }},
                                                            {{ $address->upazila }}, {{ $address->district }}, {{ $address->division }}
                                                            @if ($address->postal_code)
                                                                — {{ $address->postal_code }}
                                                            @endif
                                                        </div>
                                                        @if ($address->delivery_note)
                                                            <div class="text-muted fst-italic">“{{ $address->delivery_note }}”</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- ছোট অ্যাকশন — আলাদা form (nested form এড়াতে target ব্যবহৃত) --}}
                                                <div class="mt-2 d-flex gap-2 justify-content-end">
                                                    @can('setDefault', $address)
                                                        @unless ($address->is_default)
                                                            <button type="button" class="btn btn-link btn-sm text-success text-decoration-none p-0"
                                                                    form="address-action-form"
                                                                    formaction="{{ route('addresses.default', $address) }}"
                                                                    formnovalidate>{{ __('checkout.set_default') }}</button>
                                                        @endunless
                                                    @endcan
                                                    @can('delete', $address)
                                                        <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0"
                                                                onclick="if (confirm('আপনি কি এই ঠিকানাটি মুছে ফেলতে চান?')) { document.getElementById('delete-address-{{ $address->id }}').submit(); }">
                                                            {{ __('checkout.delete') }}
                                                        </button>
                                                    @endcan
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    {{-- নতুন ঠিকানা ফর্ম — fetch দিয়ে submit (nested form এড়াতে);
                                         validation errors বাংলায় inline দেখানো হয় --}}
                                    <div class="collapse mt-3 {{ $addresses->isEmpty() ? 'show' : '' }}" id="new-address-form">
                                        <hr>
                                        <h3 class="h6 fw-semibold">{{ __('checkout.new_address') }}</h3>
                                        <div id="new-address-fields">
                                            @include('addresses._form', ['idPrefix' => 'new-'])
                                        </div>
                                        <div id="new-address-error" class="alert alert-danger py-2 small mt-2 d-none" role="alert"></div>
                                        <button type="button" class="btn btn-success btn-sm mt-3" data-save-new-address>
                                            <i class="bi bi-save me-1"></i>{{ __('checkout.save_address') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- ডেলিভারি পদ্ধতি --}}
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white">
                                    <h2 class="h6 mb-0"><i class="bi bi-truck me-1"></i>{{ __('checkout.delivery_section') }}</h2>
                                </div>
                                <div class="card-body vstack gap-2">
                                    @foreach ($methods as $method)
                                        <label class="border rounded-3 p-3 d-flex justify-content-between align-items-center
                                                      {{ old('delivery_method', $method->value) === $method->value ? 'border-success bg-success-subtle' : '' }}"
                                               style="cursor: pointer;">
                                            <span class="form-check mb-0 d-flex align-items-center gap-2">
                                                <input type="radio" name="delivery_method" value="{{ $method->value }}"
                                                       class="form-check-input mt-0"
                                                       {{ old('delivery_method', $method->value) === $method->value ? 'checked' : '' }}>
                                                <span class="fw-semibold">{{ $method->label() }}</span>
                                            </span>
                                            <span class="fw-bold text-success">{{ \App\Support\BengaliNumber::money($summary->deliveryFee) }}</span>
                                        </label>
                                    @endforeach

                                    {{-- পেমেন্ট placeholder — settlement Phase 09+ --}}
                                    <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center bg-light">
                                        <span class="fw-semibold">
                                            <i class="bi bi-cash-coin me-1 text-success"></i>{{ __('checkout.cod_label') }}
                                        </span>
                                        <small class="text-muted">{{ __('checkout.cod_hint') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= ডান: অর্ডার সারাংশ ================= --}}
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm sticky-top" style="top: 90px;">
                                <div class="card-header bg-white">
                                    <h2 class="h6 mb-0"><i class="bi bi-receipt me-1"></i>{{ __('checkout.summary_section') }}</h2>
                                </div>
                                <div class="card-body">
                                    <h3 class="small text-muted fw-semibold mb-2">{{ __('checkout.items_title') }}</h3>
                                    <ul class="list-unstyled vstack gap-3 mb-3">
                                        @foreach ($summary->items as $item)
                                            <li class="d-flex gap-2 align-items-center">
                                                @if ($item['product']->imageUrl())
                                                    <img src="{{ $item['product']->imageUrl() }}"
                                                         alt="{{ $item['product']->imageAltText() }}"
                                                         class="rounded flex-shrink-0" loading="lazy"
                                                         style="width: 52px; height: 52px; object-fit: cover;">
                                                @else
                                                    <div class="bg-success-subtle rounded flex-shrink-0 d-flex align-items-center justify-content-center"
                                                         style="width: 52px; height: 52px;">
                                                        <span>🌾</span>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1 small min-w-0">
                                                    <div class="fw-semibold text-truncate">{{ $item['product']->name }}</div>
                                                    <div class="text-muted">{{ $item['variant']->name }} × {{ \App\Support\BengaliNumber::format($item['quantity']) }}</div>
                                                </div>
                                                <div class="small fw-semibold text-end">{{ \App\Support\BengaliNumber::money($item['line_total']) }}</div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <dl class="mb-0">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <dt class="fw-normal text-muted">{{ __('checkout.subtotal') }}</dt>
                                            <dd class="mb-0">{{ \App\Support\BengaliNumber::money($summary->subtotal) }}</dd>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <dt class="fw-normal text-muted">{{ __('checkout.delivery_fee') }}</dt>
                                            <dd class="mb-0">{{ \App\Support\BengaliNumber::money($summary->deliveryFee) }}</dd>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between">
                                            <dt class="fw-bold">{{ __('checkout.grand_total') }}</dt>
                                            <dd class="mb-0 fw-bold text-success fs-5 grand-total">
                                                {{ \App\Support\BengaliNumber::money($summary->grandTotal) }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <p class="small text-muted mt-3 mb-0">
                                        <i class="bi bi-shield-check me-1"></i>{{ __('checkout.revalidation_note') }}
                                    </p>
                                </div>
                                <div class="card-footer bg-white d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg"
                                            onclick="this.disabled = true; this.form.submit();">
                                        <i class="bi bi-check2-circle me-2"></i>{{ __('checkout.confirm') }}
                                    </button>
                                    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                                        {{ __('checkout.change_cart') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- ঠিকানা অ্যাকশনের helper forms (মূল form-এর বাইরে) --}}
                <form id="address-action-form" method="POST" action="#" class="d-none">@csrf</form>
                @foreach ($addresses as $address)
                    @can('delete', $address)
                        <form id="delete-address-{{ $address->id }}" method="POST"
                              action="{{ route('addresses.destroy', $address) }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                @endforeach
            @endif
        </div>
    </section>

    @push('scripts')
        <script>
            // বিভাগ নির্বাচনে জেলা তালিকা হালনাগাদ (config-driven, Blade-এ তালিকা ছড়ানো নয়)
            document.querySelectorAll('.js-division-select').forEach(function (select) {
                select.addEventListener('change', function () {
                    var districts = JSON.parse(this.dataset.districts || '{}')[this.value] || [];
                    var districtSelect = document.getElementById(this.id.replace('division', 'district'));
                    if (! districtSelect) return;

                    districtSelect.innerHTML = '';
                    var placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = '— নির্বাচন করুন —';
                    districtSelect.appendChild(placeholder);

                    districts.forEach(function (district) {
                        var option = document.createElement('option');
                        option.value = district;
                        option.textContent = district;
                        districtSelect.appendChild(option);
                    });
                });
            });

            // ঠিকানা radio নির্বাচনে card highlight
            document.querySelectorAll('input[name="address_id"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    document.querySelectorAll('.address-option').forEach(function (card) {
                        card.classList.remove('border-success', 'bg-success-subtle');
                    });
                    radio.closest('.address-option').classList.add('border-success', 'bg-success-subtle');
                });
            });

            // নতুন ঠিকানা — fetch submit; 422 validation errors বাংলায় inline
            var saveAddressButton = document.querySelector('[data-save-new-address]');
            if (saveAddressButton) {
                saveAddressButton.addEventListener('click', async function () {
                    var container = document.getElementById('new-address-fields');
                    var errorBox = document.getElementById('new-address-error');
                    errorBox.classList.add('d-none');

                    var payload = {};
                    container.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (field) {
                        payload[field.name] = field.type === 'checkbox' ? (field.checked ? 1 : 0) : field.value;
                    });

                    saveAddressButton.disabled = true;

                    var response = await fetch('{{ route('addresses.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    saveAddressButton.disabled = false;

                    if (response.ok) {
                        window.location.reload();
                        return;
                    }

                    var data = await response.json().catch(function () { return {}; });
                    var messages = data.errors ? Object.values(data.errors).flat() : [data.message || 'দুঃখিত, কিছু সমস্যা হয়েছে।'];
                    errorBox.innerHTML = '';
                    messages.forEach(function (message) {
                        var div = document.createElement('div');
                        div.textContent = message;
                        errorBox.appendChild(div);
                    });
                    errorBox.classList.remove('d-none');
                });
            }
        </script>
    @endpush
</x-layouts.app>
