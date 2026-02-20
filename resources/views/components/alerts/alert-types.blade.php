@php
    $languages = array_filter($this->languages, function($lang) {
        return $lang !== 'default';
    });
    $tile = min(4, count($languages));
@endphp
<div>
    <ul>
        @foreach($this->errorAlertTypes as $errorKey)
            <li class="text-sm">
                <div class="flex w-full my-4 bg-white bg-opacity-20 border border-slate-300">
                    <div class="flex flex-col px-4 py-4 w-full">
                        <div class="flex justify-between w-full border-b border-gray-400 pb-4">
                            <h2 class="text-base uppercase text-medium mb-0 text-gray-900">{{ $this->alertLabelsTranslations[$errorKey] }}</h2>

                            <div class="flex w-75 justify-between items-center">
                                <div class="w-full flex items-center">
                                    <span class="px-4">{{strtoupper(__('visible'))}}</span>
                                    @if(in_array($errorKey,['ALARM','PERIODICAL']))
                                        <div class="absolute_ right-0_ h-full flex items-center mr-4"><x-monoicon.locked class="relative text-red-600" /></div>
                                    @else
                                        <div wire:click="toggleVisibility('{{$errorKey}}')" class="btn switch @if($this->visibility[$errorKey]) active  bg-color-new @else bg-gray-400 @endif " role="switch">
                                            <span class="@if($this->visibility[$errorKey]) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="w-full flex justify-end items-center">
                                    <span class="px-4">{{strtoupper(__('Critical'))}}</span>
                                    @if(in_array($errorKey,['ALARM','PERIODICAL']))
                                        <div class="absolute_ right-0_ h-full flex items-center mr-4"><x-monoicon.locked class="relative text-red-600" /></div>
                                    @else
                                        <div wire:click="toggleCriticality('{{$errorKey}}')" class="btn switch @if($this->criticality[$errorKey]) active  bg-color-new @else bg-gray-400 @endif " role="switch">
                                            <span class="@if($this->criticality[$errorKey]) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="w-full flex justify-end items-center">
                                    <span class="px-4">{{strtoupper(__('Alarm'))}}</span>
                                    @if(in_array($errorKey,['ALARM']))
                                        <div class="absolute_ right-0_ h-full flex items-center mr-4"><x-monoicon.locked class="relative text-red-600" /></div>
                                    @else
                                        <div wire:click="toggleAlarmality('{{$errorKey}}')" class="btn switch @if($this->alarmality[$errorKey] ?? false) active  bg-color-new @else bg-gray-400 @endif " role="switch">
                                            <span class="@if($this->alarmality[$errorKey] ?? false) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row justify-between w-full py-2">
                            <div class="flex flex-col w-full md:flex-row flex-wrap">
                                @foreach($languages as $language)
                                    <x-form.group :tile="$tile" class="mb-4 w-full">
                                        <x-form.label for="{{$errorKey}}.{{$language}}" >
                                            {{$language}}
                                        </x-form.label>
                                        <x-input.text wire:model.defer="alertTranslations.{{$errorKey}}.{{$language}}"  type="text" name="{{$errorKey}}.{{$language}}" value='{{ $this->alertTranslations[$errorKey]["$language"] }}' />
                                    </x-form.group>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    <ul>
    @foreach($this->warningAlertTypes as $warningKey)
        <li class="text-sm">
            <div class="flex w-full my-4 bg-white bg-opacity-20 border border-slate-300">
                <div class="flex flex-col w-full px-4 py-4">
                    <div class="flex justify-between w-full border-b border-gray-400 pb-4">
                        <h2 class="text-base uppercase text-medium mb-0 text-gray-900">{{ $this->alertLabelsTranslations[$warningKey] }}</h2>
                        <div class="flex w-75 justify-between items-center">

                            <div class="w-full flex items-center">
                                <span class="px-4">{{strtoupper(__('visible'))}}</span>
                                @if(in_array($warningKey,['ALARM','PERIODICAL']))
                                    <div class="absolute_ right-0_ h-full flex items-center mr-4"><x-monoicon.locked class="relative text-red-600" /></div>
                                @else
                                    <div wire:click="toggleVisibility('{{$warningKey}}')" class="btn switch @if($this->visibility[$warningKey]) active  bg-color-new @else bg-gray-400 @endif " role="switch">
                                        <span class="@if($this->visibility[$warningKey]) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full flex justify-end items-center">
                                <span class="px-4">{{strtoupper(__('Critical'))}}</span>
                                @if(in_array($warningKey,['ALARM','PERIODICAL']))
                                    <div class="absolute_ right-0_ h-full flex items-center mr-4"><x-monoicon.locked class="relative text-red-600" /></div>
                                @else
                                    <div wire:click="toggleCriticality('{{$warningKey}}')" class="btn switch @if($this->criticality[$warningKey]) active  bg-color-new @else bg-gray-400 @endif " role="switch">
                                        <span class="@if($this->criticality[$warningKey]) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full flex justify-end items-center">
                                <span class="px-4">{{strtoupper(__('Alarm'))}}</span>
                                @if(in_array($warningKey,['ALARM']))
                                    <div class="absolute_ right-0_ h-full flex items-center mr-4"><x-monoicon.locked class="relative text-red-600" /></div>
                                @else
                                    <div wire:click="toggleAlarmality('{{$warningKey}}')" class="btn switch @if($this->alarmality[$warningKey] ?? []) active  bg-color-new @else bg-gray-400 @endif " role="switch">
                                        <span class="@if($this->alarmality[$warningKey] ?? []) translate-x-5 @else translate-x-0 @endif  inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between w-full py-2">
                        <div class="flex flex-col w-full md:flex-row flex-wrap">
                            @foreach($languages as $language)
                                <x-form.group :tile="$tile" class="mb-4 w-full">
                                    <x-form.label for="{{$warningKey}}.{{$language}}" >
                                        {{$language}}
                                    </x-form.label>
                                    <x-input.text wire:model.defer="alertTranslations.{{$warningKey}}.{{$language}}"  type="text" name="{{$warningKey}}.{{$language}}" value='{{ $this->alertTranslations[$warningKey]["$language"] }}' />
                                </x-form.group>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </li>
    @endforeach
</ul>
</div>
