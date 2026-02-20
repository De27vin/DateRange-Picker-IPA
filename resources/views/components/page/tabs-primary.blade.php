@props([
    'tabs' => [],
    'defaultTab' => '',
    'barMargin' => null
])

<div x-cloak x-data="{ openTab: '{{ $defaultTab ?? '' }}' }" class="py-8">
    <div class="w-full">
        <div class="bottom-underline mb-4 flex justify-between bg-white bg-opacity-60" @if($barMargin) style="margin-bottom: {{ $barMargin }};" @endif>
            <div class="flex max-w-2xl space-x-4 px-2 py-1 pr-4">
                @foreach ($tabs as $tabKey => $tabLabel)
                    <button x-on:click="openTab = '{{ $tabKey }}'"
                            :class="{ 'bg-color-new text-white': openTab === '{{ $tabKey }}' }"
                            class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue hover:bg-color-new hover:text-white">
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    <div class="w-full mx-auto mt-6">
        @foreach ($tabs as $tabKey => $tabLabel)
            <div x-show="openTab === '{{ $tabKey }}'" class="w-full">
                @if(isset(${$tabKey.'Slot'}))
                    {{ ${$tabKey.'Slot'} }}
                @endif
            </div>
        @endforeach
    </div>
</div>