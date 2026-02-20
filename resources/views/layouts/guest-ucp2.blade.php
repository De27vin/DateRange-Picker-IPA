@php
app('debugbar')->disable();
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-page.meta />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="/assets/themes/system/css/style.css">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    @stack('style')
    @livewireStyles
</head>
<body>
<div id="app" class="bg-gray-200 p-4">
    <main class="flex lg:items-center  min-h-screen lg:justify-center ">
        {{ $slot }}
    </main>
</div>

@stack('scripts')
<script src="{{ asset('assets/js/app.js') }}" defer></script>
@livewireScripts
</body>
</html>
