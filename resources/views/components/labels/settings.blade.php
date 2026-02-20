<div class="mb-4 grid grid-cols-1 lg:grid-cols-4">

    @foreach($settings as $setting)
        @php
            $convertedKey = \Str::remove('_value',\Str::replace('.', '_', $setting->setting_key));
            if (!empty($translations[$convertedKey])) {
                $label = ($translations[$convertedKey][$locale] == '' ? $translations[$convertedKey]['default'] : $translations[$convertedKey][$locale] );
            } else {
                $label = __('settings.label.'.$convertedKey);
                if ($label === 'settings.label.'.$convertedKey) {
                    $convertedKey = \Str::remove('device_',$convertedKey);
                    $label = __('settings.label.device_setting_'.$convertedKey.'_value');
                }
            }
        @endphp

        @if($setting->setting_type->st_type === 'sec')
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->deviceTypeSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>
        @elseif($setting->setting_type->st_type === 'ms')
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->deviceTypeSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>
        @elseif($setting->setting_type->st_type === 'phone')
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->labelSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>
        @elseif($setting->setting_type->st_type === 'number')
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->labelSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>
        @elseif($setting->setting_type->st_type === 'hours')
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->labelSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" wire:model="labelSettings.{{$setting->setting_id}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>
        @elseif($setting->setting_type->st_type === 'percent')
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->labelSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>
        @elseif($setting->setting_type->st_type === 'bool')
            <x-forms.grid-group class="mb-4 flex items-center">
                <div class="form-switch">
                    <input wire:model.defer="labelSettings.{{$setting->setting_id}}" type="checkbox"  name="labelSettings.{{$setting->setting_id}}" id="labelSettings.{{$setting->setting_id}}" class="form-switch-checkbox"/>
                    <label for="labelSettings.{{$setting->setting_id}}" class="form-switch-label"></label>
                </div>
                <div class="">{{ $label }}</div>
            </x-forms.grid-group>
        @else
            <x-forms.grid-group class="mb-4">
                <x-forms.label for="{{$setting->setting_key}}" :fallback="$setting->labelSettingsParentValue">
                    {{ $label }}
                </x-forms.label>
                <x-input.text wire:model.defer="labelSettings.{{$setting->setting_id}}" canWriteSettings="{{$canWriteSettings}}" wire:model="labelSettings.{{$setting->setting_id}}" type="text" name="labelSettings.{{$setting->setting_id}}" />
            </x-forms.grid-group>

        @endif
    @endforeach
</div>