@props([
    'target' => '',
    'tabs' => [],
    'defaultTab' => '',
    'verticalSpace' => false,
    'buttonsOnTop' => false,
])

<div x-cloak x-data="{ openTab: '{{ $defaultTab ?? '' }}' }">
    <div class="">
        <div class="">
            <div class="flex justify-between items-end w-full space-x-4 @if($verticalSpace) my-4 @endif">

                <div>
                    @foreach ($tabs as $tabKey => $tabLabel)
                        <button x-on:click="openTab = '{{ $tabKey }}'"
                                :class="openTab === '{{ $tabKey }}'? 'bg-color-new text-white' : 'bg-white text-gray-600 border border-slate-300'"
                                class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue">
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>

                @if($buttonsOnTop)
                    <div>
                        @foreach ($tabs as $tabKey => $tabLabel)
                            <div x-show="openTab === '{{ $tabKey }}'" wire:key="btn-slot-{{ $target }}-{{ $tabKey }}" class="w-full">
                                {{ ${$tabKey.'Buttons'} }}
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
    <div class="w-full mx-auto pt-4">
        @foreach ($tabs as $tabKey => $tabLabel)
            <div x-show="openTab === '{{ $tabKey }}'" wire:key="tab-slot-{{ $target }}-{{ $tabKey }}" class="w-full">
                {{ ${$tabKey.'Slot'} }}
            </div>
        @endforeach
    </div>
</div>