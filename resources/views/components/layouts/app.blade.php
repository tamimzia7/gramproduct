<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription ?? 'গ্রাম থেকে মাঠ, জল ও প্রকৃতি থেকে আপনার ঘরে প্রামাণ্য খাদ্য ও কৃষি পণ্য।' }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
