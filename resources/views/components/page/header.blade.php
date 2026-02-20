@if($title != '')
<div {{$attributes->merge(['class' => ''])}} >
    <div class="my-4 w-full text-sm flex justify-between bottom-underline" style="align-items: last baseline;">
        <div class="flex flex-col">
            <h1 class="text-lg text-medium mb-0 text-gray-900" id="message-heading">
                {{ $title ?? '' }}
            </h1>
            <p class="mt-1 text-base text-gray-500 overflow-hidden overflow-ellipsis">
                {{ $description ?? '' }}
            </p>
        </div>
        <div class="flex items-end sm:justify-end">
            <div class="relative pl-0 pr-1 mb-1">
                {{ $slot }}
                {{ $actionButtons ?? '' }}
            </div>
        </div>
    </div>
</div>
@endif