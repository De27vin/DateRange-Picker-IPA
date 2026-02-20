<header class="mt-0 mb-4">
    <!-- START :: Customer Logo & Brand Color -->
    <div class="bg-header w-full">
        <div class="max-w-7xl mx-auto w-full px-4 py-4 font-medium">
            <!-- <img class="items-center h-8" src="/assets/themes/system/images/logo.png" /> -->
            <img class="items-center h-10" src="/assets/themes/{{ session()->get('account.slug', 'system') }}/images/logo.png" />
        </div>
        <!-- END :: Customer Logo & Brand Color -->
    </div>
    <div class="w-full bg-white bg-opacity-60 shadow-md">
        <div class="max-w-7xl mx-auto w-full px-4 font-medium">
            <div class="relative flex h-16 justify-between w-full items-center">
                <div x-data="{ mobileOpen: false }" class=" relative h-full w-full flex items-center rounded-none ">

                    <!-- START :: Desktop Navigation Button -->
                    <div class="hidden lg:flex flex-grow py-3 justify-between rounded-none">
                        @if(\Auth::user())
                            <ul class="flex w-full items-center h-10 z-50">
                                @if( $activeAccount != null )
                                    <li class="block relative">
                                        <a href="/dashboard" class=" @if(request()->routeIs('admin') || request()->routeIs('dashboard')) bg-color-new text-white @endif flex items-center h-10 px-4 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white">
                                            <span>@lang('Dashboard')</span>
                                        </a>
                                    </li>
                                    <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                        <a href="#" class=" @if(request()->routeIs('sites') || request()->routeIs('devices-site-create')) bg-color-new text-white @endif flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                            <span>@lang('Sites')</span>
                                            <span class="ml-1">
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                        </a>
                                        <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full w-64 z-40 mt-1 -ml-36" x-show="showChildren" style="display: none;">
                                            <div class="bg-white w-full relative z-40 py-1">
                                                <ul class="list-reset">
                                                    <li class="relative">
                                                        <a href="/sites" class=" @if(request()->routeIs('sites')) bg-color-new text-white @endif px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1">@lang('Sites List')</span>
                                                        </a>
                                                    </li>
                                                    <li class="relative">
                                                        <a href="/devices-site-create" class=" @if(request()->routeIs('devices-site-create')) bg-color-new text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1">@lang('Create Site')</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                        <a href="#" class=" @if(request()->routeIs('settings.*')) bg-blue-500 text-white @endif flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-blue-500 hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                            <span>@lang('Settings')</span>
                                            <span class="ml-1">
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                        </a>
                                        <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full w-64 z-40 mt-1 -ml-36" x-show="showChildren" style="display: none;">
                                            <div class="bg-white w-full relative z-40 py-1">
                                                <ul class="list-reset">
                                                    <li class="relative">
                                                        <a href="/settings/account" class=" @if(request()->routeIs('settings.account')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1">@lang('Account')</span>
                                                        </a>
                                                    </li>
                                                    <li class="relative">
                                                        <a href="/settings/alert-types" class=" @if(request()->routeIs('settings.alert-types')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1">@lang('Alert Types')</span>
                                                        </a>
                                                    </li>
                                                    <li class="relative">
                                                        <a href="/settings/gateways" class=" @if(request()->routeIs('settings.gateways')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1">@lang('Gateways')</span>
                                                        </a>
                                                    </li>
{{--                                                    <li class="relative">--}}
{{--                                                        <a href="/settings/groups" class=" @if(request()->routeIs('settings.groups')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer"> --}}
{{--                                                            <span class="flex-1">@lang('Labels')</span> --}}
{{--                                                        </a>--}}
{{--                                                    </li>--}}
                                                    <li class="relative">
                                                        <a href="/settings/modules" class=" @if(request()->routeIs('settings.modules')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer"> 
                                                            <span class="flex-1">@lang('Modules')</span> 
                                                        </a>
                                                    </li>
                                                    <li class="relative">
                                                        <a href="/settings/users" class=" @if(request()->routeIs('settings.users')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer"> 
                                                            <span class="flex-1">@lang('Users')</span> 
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>

                            <ul class="flex w-full items-center justify-end h-10 z-50">
                                @if(Auth::user() && session('account.id') != null)
                                    <livewire:admin.alarm-notification></livewire:admin.alarm-notification>
                                @endif
                                <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                    <a href="#" class="flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-blue-500 hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                        <span>@lang('Languages')</span>
                                        <span class="ml-1">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    </a>
                                    <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full w-64 z-40 mt-1 -ml-36" x-show="showChildren" style="display: none;">
                                        <livewire:admin.language-switcher />
                                    </div>
                                </li>
                                <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                    <a href="#" class="@if(request()->routeIs('user-profile') || request()->routeIs('accounts')) bg-blue-500 text-white @endif flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-blue-500 hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                        <span>@lang('User')</span>
                                        <span class="ml-1">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    </a>
                                    <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full w-64 z-40 mt-1 -ml-36" x-show="showChildren" style="display: none;">
                                        <div class="bg-white w-full relative z-40 py-1">
                                            <ul class="list-reset">
                                                <li class="relative" >
                                                    <a href="/user-profile" class="@if(request()->routeIs('user-profile')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                        <span class="flex-1">@lang('Profile')</span>
                                                    </a>
                                                </li>
                                                @if( $activeAccount != null )
                                                    <li class="relative" >
                                                        <a href="/accounts" class="@if(request()->routeIs('accounts')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1">@lang('Switch Account')</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                <li class="relative" >
                                                    <a href="/logout" class="px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                        <span class="flex-1">@lang('Logout')</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        @elseif(!request()->routeIs('login'))
                            <ul class="flex w-full items-center justify-end h-10 z-50">
                                <li class="block relative">
                                    <a href="/login" class=" flex items-center h-10 px-4 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-blue-500 hover:text-white">
                                        <span>@lang('Login')</span>
                                    </a>
                                </li>
                            </ul>
                        @endif
                    </div>
                    <!-- END :: Desktop Navigation Button -->
                    <div class=" flex flex-grow justify-end lg:hidden">
                        @if(Auth::user() && session('account.id') != null)
                            <livewire:admin.alarm-notification></livewire:admin.alarm-notification>
                        @endif
                    </div>

                    <!-- START :: Mobile Navigation Button -->
                    <div class=" flex justify-end lg:hidden relative z-50" id="mobile-menu">
                        @if(\Auth::user())
                            <nav class="block lg:hidden">
                                <div class=" py-2 px-1 inline-flex items-center">
                                    <div class=" px-0 dropdown" x-on:keydown.window.escape="mobileOpen = false" x-on:click.away="mobileOpen = false">
                                        <button x-on:click="mobileOpen = !mobileOpen" type="button" class=" mr-2 text-gray-600 rounded-none hover:bg-blue-500 hover:rounded-none hover:text-white" aria-haspopup="true" x-bind:aria-expanded="open" aria-expanded="true">
                                            <span class="w-10 h-10 block">
                                                <svg class="block h-full w-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path xmlns="http://www.w3.org/2000/svg" d="M4 7C4 6.44772 4.44772 6 5 6H19C19.5523 6 20 6.44772 20 7C20 7.55228 19.5523 8 19 8H5C4.44772 8 4 7.55228 4 7ZM4 12C4 11.4477 4.44772 11 5 11H19C19.5523 11 20 11.4477 20 12C20 12.5523 19.5523 13 19 13H5C4.44772 13 4 12.5523 4 12ZM4 17C4 16.4477 4.44772 16 5 16H19C19.5523 16 20 16.4477 20 17C20 17.5523 19.5523 18 19 18H5C4.44772 18 4 17.5523 4 17Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </nav>
                        @endif
                    </div>
                    <!-- END :: Mobile Navigation Button -->

                    <!-- START :: Mobile Navigation -->
                    <div class=" absolute top-0 mt-16 p-8 pt-2 w-full lg:hidden z-40" x-show="mobileOpen">
                        <div class="w-full bg-white px-4 shadow-lg">

                            @if(\Auth::user())
                                <div class="py-8" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">

                                    <a href="/dashboard" class=" @if(request()->routeIs('sites')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                        @lang('Dashboard')
                                    </a>
                                    <div class="pt-8 px-4 opacity-60 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class="">@lang('Sites')</span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <a href="/sites" class=" @if(request()->routeIs('sites')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Sites List')
                                        </a>
                                        <a href="/devices-site-create" class=" @if(request()->routeIs('devices-site-create')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Create Site')
                                        </a>
                                    </div>
                                    <div class="pt-8 px-4 opacity-60 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class="">@lang('Settings')</span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <a href="/settings/account" class=" @if(request()->routeIs('settings.account')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Account')
                                        </a>
                                        <a href="/settings/alert-types" class=" @if(request()->routeIs('settings.alert-types')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Alert Types')
                                        </a>
                                        <a href="/settings/gateways" class=" @if(request()->routeIs('settings.gateways')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Gateways')
                                        </a>
                                        {{--                                            <a href="/settings/groups" class=" @if(request()->routeIs('settings.groups')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">--}}
                                        {{--                                                @lang('Labels')--}}
                                        {{--                                            </a>--}}
                                        <a href="/settings/modules" class=" @if(request()->routeIs('settings.modules')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Modules')
                                        </a>
                                        <a href="/settings/users" class=" @if(request()->routeIs('settings.users')) bg-blue-500 text-white @endif px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Users')
                                        </a>
                                    </div>
                                    <div class="pt-8 px-4 opacity-40 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class="">@lang('Languages')</span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <a href="/lang/en" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('English') </a>
                                        <a href="/lang/de" class="active px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('German') </a>
                                        <a href="/lang/fr" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('French') </a>
                                        <a href="/lang/it" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Italian') </a>
                                        <a href="/lang/es" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Spanish') </a>
                                    </div>
                                    <div class="pt-8 px-4 opacity-40 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class="">@lang('User')</span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <a href="/user-profile" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Profile') </a>
                                        <a href="/accounts" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Switch Account')</a>
                                        <a href="/logout" class=" px-4 py-2 flex w-full items-start hover:bg-blue-500 hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            @lang('Logout')</a>
                                    </div>

                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>