<x-layouts.error>
    <div class="h-screen w-screen flex justify-center content-center flex-col" style="background-color: rgb(160,80,80);">
        <p class="text-center font-sans lowercase font-bold text-6xl mb-16" style="color:#eaeaea;">{{ __('UCP Error') }}</p>
        <div class="flex flex-col mt-8 w-full md:w-11/12 lg:w-5/6 mx-auto relative">
            @if(isset($message))
                {!! $message !!}
                <div class="w-full flex justify-end mt-8">
                    <x-form.button color="info"  onClick="window.parent.location.reload()">{{ __('Reload') }}</x-form.button>
                    <x-form.button color="warning" onClick="window.parent.location.href='/logout'">{{ __('Logout') }}</x-form.button>
                </div>
            @else
                <ul class="errormessage"><li><span class="title">{{ __('error') }}</span><span>{{ $exception->getMessage() }}</span></li></ul>
                <div class="w-full mt-8 text-lg opacity-60 text-white">
                   {!! __('We have received an email with more details about the occurred error and try our best to fix the bug as soon as possible.<br/>Next, you can try to reload the page. If the error still there, try to logout an login again.') !!}
                </div>
                <div class="w-full flex justify-end mt-8">
                    <x-form.button color="info"  onClick="window.parent.location.reload()">{{ __('Reload') }}</x-form.button>
                    <x-form.button color="warning" onClick="window.parent.location.href='/logout'">{{ __('Logout') }}</x-form.button>
                </div>
            @endif
        </div>
    </div>
</x-layouts.error>

