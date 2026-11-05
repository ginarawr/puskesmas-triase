{{--
  Layout utama. Ganti isi dengan slot:
    <x-slot:header> ... </x-slot:header>   (opsional: navbar)
    isi utama                              (slot default)
    <x-slot:footer> ... </x-slot:footer>   (opsional: default <x-ui.footer />)
  width: wide | narrow
--}}
@props(['title' => null, 'width' => 'wide'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="refresh-seconds" content="{{ config('triase.refresh_seconds') }}">
    <title>{{ $title ? $title . ' – ' : '' }}{{ config('triase.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%23006b66'/%3E%3Cpath d='M12 6v12M6 12h12' stroke='white' stroke-width='2.5' stroke-linecap='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="app">
    @isset($header){{ $header }}@endisset
    <main class="main">
        <div class="container container-{{ $width }}">{{ $slot }}</div>
    </main>
    @isset($footer){{ $footer }}@else<x-ui.footer />@endisset
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
