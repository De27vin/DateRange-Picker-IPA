{{--errors.ucp-error--}}
<x-layouts.error>
   <div class="h-screen w-screen flex justify-center content-center flex-col bg-white">
       <div class="text-center mb-8">
           <p class="font-sans font-semibold text-4xl text-gray-800 mb-2">{{ __('Something went wrong') }}</p>
           <p class="text-gray-600">{{ __('Please contact UCP team if the problem persists') }}</p>
       </div>
       <div class="flex flex-col mt-4 w-full md:w-11/12 lg:w-5/6 mx-auto relative">
           @if(isset($message))
               @if($message)
                   <div class="bg-gray-100 p-5 rounded-lg shadow-sm">
                       <ul class="text-center">{!! $message !!}</ul>
                   </div>
               @endif
               <div class="w-full flex justify-center @if($message) mt-8 @endif space-x-4">
                   <x-form.button color="none" onClick="window.parent.location.reload()">{{ __('Refresh Page') }}</x-form.button>
                   <x-form.button color="none" onClick="window.parent.location.href='/logout'">{{ __('Re-login') }}</x-form.button>
               </div>
           @else
               <div class="bg-gray-100 p-5 rounded-lg shadow-sm">
                   <ul class="errormessage text-gray-700">
                       <li><span class="title font-medium">{{ __('Error') }}:</span> <span>{{ $exception->getMessage() }}</span></li>
                   </ul>
               </div>
               <div class="w-full mt-6 text-center text-gray-600">
                   <p class="mb-4">{{ __('Please try refreshing the page. If the problem persists, try signing out and back in again.') }}</p>
               </div>
               <div class="w-full flex justify-center mt-4 space-x-4">
                   <x-form.button color="primary" onClick="window.parent.location.reload()">{{ __('Refresh Page') }}</x-form.button>
                   <x-form.button color="secondary" onClick="window.parent.location.href='/logout'">{{ __('Sign Out') }}</x-form.button>
               </div>
           @endif
       </div>
   </div>
</x-layouts.error>