<header class="mt-0">
    <!-- START :: Customer Logo & Brand Color -->
    <div class="bg-header w-full">
        <div class="mx-auto w-full px-4 py-4 font-medium">
            <!-- <img class="items-center h-8" src="/assets/themes/system/images/logo.png" /> -->
            <img class="items-center h-10" src="/assets/themes/<?php echo e(session()->get('account.slug', 'system')); ?>/images/logo.png" />
        </div>
        <!-- END :: Customer Logo & Brand Color -->
    </div>
    <div class="mx-auto w-full mx-5 bg-white bg-opacity-60">
        <div class="mx-auto w-full px-4 py-3 font-medium">
            <div class="relative flex h-16 justify-between w-full items-center">
                <div x-cloak x-data="{ mobileOpen: false }" class=" relative h-full w-full flex items-center rounded-none ">

                    <!-- START :: Desktop Navigation Button -->
                    <div class="hidden lg:flex flex-grow py-3 justify-between rounded-none mx-4">
                        <?php if(\Auth::user()): ?>
                            <ul class="flex w-full items-center h-10 z-50">
                                <?php if( $activeAccount != null ): ?>
                                    <li class="block relative">
                                        <a href="/dashboard" <?php if(request()->routeIs('admin') || str_contains(request()->path(), 'dashboard')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="flex items-center h-10 px-4 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white">
                                            <span><?php echo app('translator')->get('Dashboard'); ?></span>
                                        </a>
                                    </li>
                                    <li class="block relative">
                                        <a href="/equipment" <?php if(request()->routeIs('equipment') || request()->routeIs('devices-site-create')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="flex items-center h-10 px-4 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white">
                                            <span><?php echo app('translator')->get('Equipment'); ?></span>
                                        </a>
                                    </li>

                                    <li class="block relative">
                                        <a href="/charts" <?php if(request()->routeIs('charts')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="flex items-center h-10 px-4 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white">
                                            <span><?php echo app('translator')->get('Charts'); ?></span>
                                        </a>
                                    </li>



























                                    <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                        <a href="#" <?php if(request()->routeIs('settings.*')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                            <span><?php echo app('translator')->get('Settings'); ?></span>
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
                                                        <a href="/settings/account" <?php if(request()->routeIs('settings.account')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1"><?php echo app('translator')->get('Account'); ?></span>
                                                        </a>
                                                    </li>





                                                    <li class="relative">
                                                        <a href="/settings/gateways" <?php if(request()->routeIs('settings.gateways')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1"><?php echo app('translator')->get('Gateways'); ?></span>
                                                        </a>
                                                    </li>
                                                    <li class="relative">
                                                        <a href="/settings/modules" <?php if(request()->routeIs('settings.modules')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1"><?php echo app('translator')->get('Modules'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php if(config('ucp.active_labels')): ?>
                                                        <li class="relative">
                                                            <a href="/settings/labels" class=" <?php if(request()->routeIs('settings.labels')): ?> bg-color-new text-white <?php endif; ?> px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                                <span class="flex-1"><?php echo app('translator')->get('Labels'); ?></span>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li class="relative">
                                                        <a href="/settings/users" <?php if(request()->routeIs('settings.users')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1"><?php echo app('translator')->get('Users'); ?></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <ul class="flex w-full items-center justify-end h-10 z-50">
                                <?php if(Auth::user() && session('account.id') != null): ?>
                                    <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('admin.alarm-notification', [])->html();
} elseif ($_instance->childHasBeenRendered('l2273444989-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l2273444989-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l2273444989-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l2273444989-0');
} else {
    $response = \Livewire\Livewire::mount('admin.alarm-notification', []);
    $html = $response->html();
    $_instance->logRenderedChild('l2273444989-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:admin.alarm-notification>
                                <?php endif; ?>














                                <li class="block relative" x-data="{showChildren:false}" x-on:click.away="showChildren=false">
                                    <a href="#" <?php if(request()->routeIs('user-profile') || request()->routeIs('accounts')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="flex items-center h-10 pl-4 pr-2 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white" x-on:click.prevent="showChildren=!showChildren">
                                        <span><?php echo app('translator')->get('User'); ?></span>
                                        <span class="ml-1">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    </a>
                                    <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full w-64 z-40 mt-1 -ml-36" x-show="showChildren" style="display: none;">
                                        <div class="bg-white w-full relative z-40 py-1">
                                            <ul class="list-reset">
                                                <!-- Added User Name Display -->
                                                <li class="relative px-4 py-3 border-b border-gray-200">
                                                    <span class="block text-gray-600 font-medium"><?php echo e(Auth::user()->user_firstname); ?> <?php echo e(Auth::user()->user_lastname); ?></span>
                                                </li>
                                                <li class="relative" >
                                                    <a href="/user-profile" <?php if(request()->routeIs('user-profile')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                        <span class="flex-1"><?php echo app('translator')->get('Profile'); ?></span>
                                                    </a>
                                                </li>
                                                <?php if( $activeAccount != null ): ?>
                                                    <li class="relative" >
                                                        <a href="/accounts" <?php if(request()->routeIs('accounts')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                            <span class="flex-1"><?php echo app('translator')->get('Switch Account'); ?></span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li class="relative" >
                                                    <a href="/logout" class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                        <span class="flex-1"><?php echo app('translator')->get('Logout'); ?></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        <?php elseif(!request()->routeIs('login')): ?>
                            <ul class="flex w-full items-center justify-end h-10 z-50">
                                <li class="block relative">
                                    <a href="/login" class=" flex items-center h-10 px-4 rounded-full cursor-pointer no-underline hover:no-underline transition-colors duration-100 mx-1 hover:bg-color-new hover:text-white">
                                        <span><?php echo app('translator')->get('Login'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- END :: Desktop Navigation Button -->
                    <div class=" flex flex-grow justify-end lg:hidden">
                        <?php if(Auth::user() && session('account.id') != null): ?>
                            <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('admin.alarm-notification', [])->html();
} elseif ($_instance->childHasBeenRendered('l2273444989-1')) {
    $componentId = $_instance->getRenderedChildComponentId('l2273444989-1');
    $componentTag = $_instance->getRenderedChildComponentTagName('l2273444989-1');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l2273444989-1');
} else {
    $response = \Livewire\Livewire::mount('admin.alarm-notification', []);
    $html = $response->html();
    $_instance->logRenderedChild('l2273444989-1', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:admin.alarm-notification>
                        <?php endif; ?>
                    </div>

                    <!-- START :: Mobile Navigation Button -->
                    <div class=" flex justify-end lg:hidden relative z-50" id="mobile-menu">
                        <?php if(\Auth::user()): ?>
                            <nav class="block lg:hidden">
                                <div class=" py-2 px-1 inline-flex items-center">
                                    <div class=" px-0 dropdown" x-on:keydown.window.escape="mobileOpen = false" x-on:click.away="mobileOpen = false">
                                        <button x-on:click="mobileOpen = !mobileOpen" type="button" class=" mr-2 text-gray-600 rounded-none hover:bg-color-new hover:rounded-none hover:text-white" aria-haspopup="true" x-bind:aria-expanded="open" aria-expanded="true">
                                            <span class="w-10 h-10 block">
                                                <svg class="block h-full w-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path xmlns="http://www.w3.org/2000/svg" d="M4 7C4 6.44772 4.44772 6 5 6H19C19.5523 6 20 6.44772 20 7C20 7.55228 19.5523 8 19 8H5C4.44772 8 4 7.55228 4 7ZM4 12C4 11.4477 4.44772 11 5 11H19C19.5523 11 20 11.4477 20 12C20 12.5523 19.5523 13 19 13H5C4.44772 13 4 12.5523 4 12ZM4 17C4 16.4477 4.44772 16 5 16H19C19.5523 16 20 16.4477 20 17C20 17.5523 19.5523 18 19 18H5C4.44772 18 4 17.5523 4 17Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>
                    <!-- END :: Mobile Navigation Button -->

                    <!-- START :: Mobile Navigation -->
                    <div class=" absolute top-0 mt-16 p-8 pt-2 w-full lg:hidden z-40" x-show="mobileOpen">
                        <div class="w-full bg-white px-4 shadow-lg">

                            <?php if(\Auth::user()): ?>
                                <div class="py-8" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">

                                    <a href="/dashboard" <?php if(request()->routeIs('dashboard')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?>  class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                        <?php echo app('translator')->get('Dashboard'); ?>
                                    </a>
                                    <div class="pt-8 px-4 opacity-60 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class=""><?php echo app('translator')->get('Equipment'); ?></span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <a href="/equipment" <?php if(request()->routeIs('equipment')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Equipment'); ?>
                                        </a>



                                    </div>

                                    <div class="mx-4">
                                        <a href="/charts" <?php if(request()->routeIs('charts')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Charts'); ?>
                                        </a>
                                    </div>
                                    
                                    <div class="pt-8 px-4 opacity-60 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class=""><?php echo app('translator')->get('Settings'); ?></span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <a href="/settings/account" <?php if(request()->routeIs('settings.account')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Account'); ?>
                                        </a>



                                        <a href="/settings/gateways" <?php if(request()->routeIs('settings.gateways')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Gateways'); ?>
                                        </a>
                                        <a href="/settings/modules" <?php if(request()->routeIs('settings.modules')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Modules'); ?>
                                        </a>
                                        <?php if(config('ucp.active_labels')): ?>
                                            <a href="/settings/labels" class=" <?php if(request()->routeIs('settings.labels')): ?> bg-color-new text-white <?php endif; ?> px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                                <?php echo app('translator')->get('Labels'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/settings/users" <?php if(request()->routeIs('settings.users')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class="px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Users'); ?>
                                        </a>
                                    </div>

















                                    <div class="pt-8 px-4 opacity-40 relative">
                                        <div class="-mt-4 relative flex justify-start">
                                            <span class=""><?php echo app('translator')->get('User'); ?></span>
                                        </div>
                                    </div>
                                    <div class="mx-4">
                                        <div class="relative px-4 py-3 border-b border-gray-200">
                                            <span class="block text-gray-600 font-medium"><?php echo e(Auth::user()->user_firstname); ?> <?php echo e(Auth::user()->user_lastname); ?></span>
                                        </div>
                                        <a href="/user-profile" <?php if(request()->routeIs('user-profile')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class=" px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Profile'); ?> </a>
                                        <a href="/accounts"  <?php if(request()->routeIs('accounts')): ?> style="background-color: #8fabdd; color: white;" <?php endif; ?> class=" px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Switch Account'); ?></a>
                                        <a href="/logout" class=" px-4 py-2 flex w-full items-start hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer" role="menuitem">
                                            <?php echo app('translator')->get('Logout'); ?></a>
                                    </div>

                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-underline" style="margin-inline: 1.22rem;"></div>

    </div>
</header><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/admin/navigation-new.blade.php ENDPATH**/ ?>