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
    </head>
    <body>
        <div id="app" class="bg-gray-200">
            <main class="flex lg:items-center  min-h-screen lg:justify-center ">
                {{ $slot }}
            </main>
        </div>

        <livewire:scripts/>
        {{-- <script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.js"></script> --}}
        <x-laravel-blade-sortable::scripts/>

        <script src="{{ asset('assets/js/app.js') }}" ></script>
        @stack('scripts')
    </body>

    <style>
        /* Error message styling */
        .errormessage {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .errormessage li {
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .errormessage .title {
            font-weight: 600;
            display: inline-block;
            min-width: 80px;
        }
    </style>
</html>
