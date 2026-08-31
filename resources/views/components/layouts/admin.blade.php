@props(['title' => 'অ্যাডমিন প্যানেল'])

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name', 'Gram Product') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">

    @stack('head')

    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js', 'resources/js/admin.js'])
</head>
<body class="admin-body">
    {{ $slot }}
</body>
</html>
