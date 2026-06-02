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
        <link rel="stylesheet" type="text/css" href="/assets/themes/{{ session()->get('account.slug', 'system') }}/css/style.css">
        <link rel="preload" href="{{ asset('assets/fonts/f7icons/Framework7Icons-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link href="{{ asset('assets/plugins/fontawesome/css/iconfonts.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
        @stack('style')
        <livewire:styles/>
    </head>
    <body class="bg-gray-200">
        <div class="bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('/assets/images/bg-ucp.jpg');">
            <div id="app" class="min-h-screen flex flex-col justify-start">
                {{-- this navigation is deprecated --}}
                <livewire:admin.navigation />
                {{ $slot }}
            </div>
        </div>
        <livewire:scripts/>

        <script src="{{ asset('assets/js/app.js') }}" ></script>
        @stack('scripts')
    </body>
</html>
