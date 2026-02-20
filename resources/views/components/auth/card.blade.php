@props(['hasUpdate' => false])
<div {!! $attributes->merge([
    'class' =>
        'px-4 sm:px-6 lg:px-8'
]) !!}>
    <div  class=" @if($hasUpdate)relative blur-lg cursor-not-allowed pointer-events-none @endif overflow-hidden shadow-lg bg-gray-300 bg-opacity-20">
        <div class="flex flex-col max md:flex-row md:flex-1 lg:max-w-screen-md">
            <div class="p-8 md:w-1/2 md:flex-shrink-0 md:flex md:flex-col items-start">
                <a href="https://serv24.com/en/solutions/ucp">
                    <h3 class="mb-4">@lang('UCP')</h3>
                    <span class="text-base">@lang('Universal Convergence Platform')</span>
                </a>
                <p class="mt-6 opacity-70 md:mt-0">
                    @lang('Cloud based alarm call handling system with multivendor alarm device support for PSTN and IP based communication. Meets the industry-specific requirements of EN81-28.')
                </p>
                <p class="flex flex-col mt-8">
                    <span>
                        @lang('You have no account?')
                    </span>
                    <span>
                        @lang('Do not hesitate and contact us today!') <a class="underline" href="sales@serv24.com">sales@serv24.com</a>
                    </span>
                </p>
            </div>
            <div class="p-8 md:flex-1">
                <h3 class="mb-4">
                    {{ $title ?? ''}}
                </h3>
                {{ $slot }}
            </div>
        </div>
        <div class="w-full px-8 py-1 ">
            <div class="w-full border-t border-gray-400"></div>
            <div class="text-xs float-right inline-block items-end my-4">
                <a class="" href="https://serv24.com/terms">
                    @lang('Terms & Conditions')
                </a>
            </div>

        </div>
    </div>
</div>
