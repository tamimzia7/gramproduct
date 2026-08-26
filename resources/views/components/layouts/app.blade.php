@props(['title' => null, 'metaDescription' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription ?? 'গ্রাম, মাঠ ও নদী থেকে সংগৃহীত খাঁটি গ্রামীণ খাদ্য ও কৃষিপণ্য সরাসরি আপনার ঘরে।' }}">

    <title>{{ $title ?? config('app.name', 'Gram Product') }}</title>

    {{-- অ্যাপ্লিকেশন আইকন (ফেভিকন) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-navbar />

    <main>
        {{ $slot ?? '' }}
    </main>

    <x-footer />

    @stack('scripts')
</body>
</html>
