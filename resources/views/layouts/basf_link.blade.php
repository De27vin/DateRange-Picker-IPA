<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-page.meta />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="account-id" content="{{ session('account.id', '') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="/assets/themes/{{ session()->get('account.slug', 'system') }}/css/style.css">
    <link rel="preload" href="{{ asset('assets/fonts/f7icons/Framework7Icons-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link href="{{ asset('assets/plugins/fontawesome/css/iconfonts.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="/assets/css/app2.css?id=12" rel="stylesheet">
    @stack('style')
    <livewire:styles/>
</head>
<body class="bg-white text-normal" style="zoom: 0.72;">
<div>
    <x-page.loading-indicator />
    <div id="app" class="min-h-screen flex flex-col justify-start">
        <x-basf_link.navigation/>

        <div class="mb-8"></div>

        <!--  BEGIN :: content -->
        <div class="pb-48">
            {{ $slot }}
        </div>
        <!--  END :: content -->

    </div>
</div>
<i class="absolute w-0 h-0 hidden fas fa-caret-right fa-caret-down md:w-1/2 lg:w-1/3 lg:w-1/4 devicebox"></i>
<div class="absolute w-0 h-0 hidden text-gray-400 fas fa-caret-down fa-caret-right text-purple-400 text-missing-400 text-warning-400 text-error-400 w-64 w-48 w-40 w-32 -ml-8 ml-0  bg-warnings-600 bg-missings-600 bg-errors-600 bg-color-new-600 bg-green-600 bg-success-400 bg-success-600 bg-warnings-400 bg-warnings-600 text-color-new-800 text-green-800 bg-gray-600 text-gray-600 text-gray-800" ></div>
<div class="absolute w-0 h-0 hidden bg-red-200 bg-color-new-200 bg-green-200 bg-orange-200 hover:border-color-new-600 border-infos-200 border-infos-400 hover:border-infos-600 border-warnings-200 border-warnings-400 hover:border-warnings-600 border-errors-200 border-errors-400 hover:border-errors-600 text-infos-200 text-infos-400 text-infos-600 text-warnings-200 text-warnings-400 text-warnings-600 text-errors-200 text-errors-400 text-errors-600  bg-infos-200 bg-infos-400 bg-infos-600 bg-warnings-200 bg-warnings-400 bg-warnings-600 bg-errors-200 bg-errors-400 bg-errors-600"></div>
<livewire:scripts/>
{{--<x-laravel-blade-sortable::scripts/>--}}
<script src="{{ asset('assets/js/app.js') }}" ></script>
@stack('scripts')
{{--@livewire('livewire-ui-spotlight')--}}

<x-notification ></x-notification>
{{-- <x:notify-messages /> --}}
<script type="text/javascript">

    window.livewire.onError(statusCode => {
        if (statusCode === 419) {
            window.location.href = "{{ route('login') }}"
        } else {
            return false
        }
    })


    // Set the idle time threshold (in milliseconds)
    const IDLE_TIME_THRESHOLD = 3600000; // 60 minutes

    let idleTimer;

    // Start the idle timer
    function startIdleTimer() {
      idleTimer = setTimeout(logoutUser, IDLE_TIME_THRESHOLD);
    }

    // Reset the idle timer
    function resetIdleTimer() {
      clearTimeout(idleTimer);
      startIdleTimer();
    }

    // Log out the user
    function logoutUser() {
      // Perform the logout operation, e.g., redirect to the logout page or send an API request to invalidate the session
      window.location.href = '/logout';
    }

    // Add event listeners to detect user activity
    document.addEventListener('mousemove', resetIdleTimer);
    document.addEventListener('keydown', resetIdleTimer);
    document.addEventListener('click', resetIdleTimer);

    startIdleTimer();

</script>

</body>
</html>
