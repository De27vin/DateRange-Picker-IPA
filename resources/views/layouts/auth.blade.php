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
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app-new.css') }}" rel="stylesheet">
    @stack('style')
    @livewireStyles
</head>
    <body class="bg-gray-200">
        {{--}}
        <div id="app" class=" p-4 " style="
            background: url('/assets/images/bg-ucp.jpg');
            background-repeat: no-repeat;
            background-size: cover;">
          --}}
        @include('components.page.navigation')
        <main class="flex lg:items-center  min-h-screen lg:justify-center ">
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
    @livewireScripts

</body>
</html>
