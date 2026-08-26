<x-layouts.app :title="__('checkout.success_title')">
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 text-center">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h1 class="h3 mt-3 mb-2">{{ __('checkout.success_title') }}</h1>
                    <p class="text-muted mb-1">
                        {{ __('checkout.order_number') }}:
                        <strong><code>{{ $order->order_number }}</code></strong>
                    </p>
                    <p class="text-muted">{{ __('checkout.success_message') }}</p>

                    <div class="card border-0 shadow-sm text-start mt-4">
                        <div class="card-body">
                            <h2 class="h6 mb-3">{{ __('checkout.summary_section') }}</h2>
                            <ul class="list-unstyled small vstack gap-2 mb-3">
                                @foreach ($order->items as $item)
                                    <li class="d-flex justify-content-between gap-2">
                                        <span>
                                            {{ $item->product_name }}
                                            <span class="text-muted">({{ $item->variant_name }} × {{ \App\Support\BengaliNumber::format($item->quantity) }})</span>
                                        </span>
                                        <span class="fw-semibold">{{ \App\Support\BengaliNumber::money($item->line_total) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <dl class="mb-0 small">
                                <div class="d-flex justify-content-between">
                                    <dt class="fw-normal text-muted">{{ __('checkout.subtotal') }}</dt>
                                    <dd class="mb-0">{{ \App\Support\BengaliNumber::money($order->subtotal) }}</dd>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <dt class="fw-normal text-muted">{{ __('checkout.delivery_fee') }}</dt>
                                    <dd class="mb-0">{{ \App\Support\BengaliNumber::money($order->delivery_fee) }}</dd>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <dt class="fw-bold">{{ __('checkout.grand_total') }}</dt>
                                    <dd class="mb-0 fw-bold text-success">{{ \App\Support\BengaliNumber::money($order->grand_total) }}</dd>
                                </div>
                            </dl>
                            <p class="small text-muted mt-3 mb-0">
                                <i class="bi bi-truck me-1"></i>{{ $order->receiver_name }} — {{ $order->district }},
                                {{ $order->upazila }}<br>
                                <i class="bi bi-cash-coin me-1"></i>{{ __('checkout.payment_note') }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('home') }}" class="btn btn-success mt-4 px-4">
                        {{ __('checkout.back_home') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
