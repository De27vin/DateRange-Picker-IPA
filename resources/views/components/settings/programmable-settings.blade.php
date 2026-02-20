{{--programmable-settings.balde.php--}}
@props(['programmableSettings', 'model', 'updateMethodName', 'target'])

@php uasort($programmableSettings, fn ($a, $b) => strcmp(strtolower($a['key']), strtolower($b['key']))); @endphp

@if(count($programmableSettings))
    <form wire:submit.prevent.stop="{{ $updateMethodName }}">
        <div class="md:flex flex-wrap mb-4">
            @foreach($programmableSettings as $key => $setting)
                @if($setting['type'] == 'bool')
                    <x-forms.group class="mb-4 pl-4 flex items-center justify-between">
                            <span class="mr-3">
                                <span class="text-sm text-medium text-gray-800">{{ $setting['translation'] }}</span>
                            </span>
                        <x-settings.bool-input
                                wire:key="{{$target}}-{{$key}}"
                                key="{{$key}}"
                                :model="$programmableSettings"
                                :fallback="$setting['fallback']['value']"
                                :readonly="!$setting['is_writeable']"
                                :fallback="$setting['fallback'] ?? null"
                        ></x-settings.bool-input>
                    </x-forms.group>
                @else
                    <x-settings.text-input
                            wire:key="{{$target}}-{{$key}}"
                            for="{{$key}}"
                            :fallback="$setting['fallback'] ?? null"
                            :readonly="!$setting['is_writeable']"
                            :settingId="$key"
                            :valueModel="$model"
                    >{{ $setting['translation'] }}
                    </x-settings.text-input>
                @endif
            @endforeach

        </div>
    </form>
@else
    <p class="text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400">@lang('Protocol does not provide programmable settings or you do not have sufficient role for reading them.')</p>
@endif