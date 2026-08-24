@props(['product'])

@if ($product->is_active)
    <span class="badge text-bg-success">সক্রিয়</span>
@else
    <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
@endif
