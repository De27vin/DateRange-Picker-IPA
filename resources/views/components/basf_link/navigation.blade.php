<header class="mt-0">
    <!-- START :: Customer Logo & Brand Color -->
    <div class="bg-header w-full">
        <div class="mx-auto w-full px-4 py-4 font-medium">
            <!-- <img class="items-center h-8" src="/assets/themes/system/images/logo.png" /> -->
            <img class="items-center h-10" src="/assets/themes/{{ session()->get('account.slug', 'system') }}/images/logo.png" />
        </div>
        <!-- END :: Customer Logo & Brand Color -->
    </div>
    <div class="mx-auto w-full mx-5 bg-white bg-opacity-60">
        <div class="mx-auto w-full px-4 py-3 font-medium">
            <div class="relative flex h-16 justify-between w-full items-center">
                <div x-data="{ mobileOpen: false }" class=" relative h-full w-full flex items-center rounded-none ">

                    <!-- START :: Desktop Navigation Button -->
                    <div class="hidden lg:flex flex-grow py-3 justify-between rounded-none">
                        <ul class="flex w-full items-center justify-end h-10 z-50">
                            <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                <a href="#" class="flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                    <span>@lang('Languages')</span>
                                    <span class="ml-1">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                </a>
                                <div class="bg-white shadow-md border border-slate-300 absolute top-auto left-0 min-w-full w-64 z-40 mt-1 -ml-36" x-show="showChildren" style="display: none;">
                                    <livewire:admin.language-switcher />
                                </div>
                            </li>
                        </ul>

                    </div>

                </div>
            </div>
        </div>
    </div>
</header>