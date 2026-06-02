@props(['wireMethod'])
<div
    x-show="showFormat"
    x-cloak
    x-on:click="showFormat = false"
    class="fixed inset-0 z-40"
></div>

<div
    x-show="showFormat"
    x-cloak
    x-on:click.stop
    class="absolute right-0 mt-2 w-72 pr-4 rounded-xl shadow-lg border border-gray-200 bg-white overflow-hidden z-50"
>
    <button
        type="button"
        x-on:click="showFormat = false; $wire['{{ $wireMethod }}']('csv', 'browser')"
        class="block w-full text-left px-5 py-3.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
    >
        @lang('CSV – Download')
    </button>
    <button
        type="button"
        x-on:click="showFormat = false; $wire['{{ $wireMethod }}']('csv', 'email')"
        class="block w-full text-left px-5 py-3.5 text-sm font-medium text-gray-700 hover:bg-gray-100 border-t border-gray-100 transition-colors"
    >
        @lang('CSV – Email')
    </button>
    <div class="h-px bg-gray-200"></div>
    <button
        type="button"
        x-on:click="showFormat = false; $wire['{{ $wireMethod }}']('xlsx', 'browser')"
        class="block w-full text-left px-5 py-3.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
    >
        @lang('Excel (XLSX) – Download')
    </button>
    <button
        type="button"
        x-on:click="showFormat = false; $wire['{{ $wireMethod }}']('xlsx', 'email')"
        class="block w-full text-left px-5 py-3.5 text-sm font-medium text-gray-700 hover:bg-gray-100 border-t border-gray-100 transition-colors"
    >
        @lang('Excel (XLSX) – Email')
    </button>
</div>

{{--
    Email sent toast — appears after job completes (managed by export-handler.js).
    Same absolute position as the format dropdown and progress bar: right below the trigger button.
    Auto-dismisses after 3 seconds.
--}}
<div
    x-show="emailSent"
    x-cloak
    class="absolute right-0 mt-2 w-96 z-50 flex items-center gap-3 bg-white border border-green-300 rounded-xl shadow-lg px-5 py-4"
>
    <span class="text-green-500 text-xl leading-none flex-shrink-0 pr-2 m-1">✓</span>
    <span class="text-small text-gray-700">@lang('Export will be sent to your email')</span>
</div>
