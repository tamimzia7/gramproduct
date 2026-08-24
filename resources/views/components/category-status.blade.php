@props(['category'])

@if ($category->is_active)
    <span class="badge text-bg-success">সক্রিয়</span>
@else
    <span class="badge text-bg-secondary">নিষ্ক্রিয়</span>
@endif

@if ($category->is_featured)
    <span class="badge text-bg-warning text-dark">বৈশিষ্ট্যযুক্ত</span>
@endif
