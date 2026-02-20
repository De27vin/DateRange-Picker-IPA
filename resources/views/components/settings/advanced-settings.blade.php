{{--advanced-settings.blade.php--}}
@props(['advancedSettings', 'model', 'updateMethodName'])

@php uasort($advancedSettings, fn ($a, $b) => strcmp(strtolower($a['key']), strtolower($b['key']))); @endphp

@if(count($advancedSettings))
    <form wire:submit.prevent.stop="{{ $updateMethodName }}">

        <div class="mb-4 font-light">

            <table class="w-full table-auto">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">{{ __('Setting') }}</th>
                        <th class="border px-4 py-2">{{ __('Fallback level') }}</th>
                        <th class="border px-4 py-2">{{ __('Fallback value') }}</th>
                        <th class="border px-4 py-2">{{ __('Type') }}</th>
                        <th class="border px-4 py-2">{{ __('Value') }}</th>
{{--                        <th class="border px-4 py-2">redaonly</th>--}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($advancedSettings as $id => $setting)
                        <tr>
                            <td class="border px-4 py-2" style="font-size: 1rem;">{{ $setting['key'] }}</td>
                            <td class="border px-4 py-2" style="font-size: 1rem;">{{ str_replace(__('Fallback:').' ', '', $setting['fallback']['label']) }}</td>
                            <td class="border px-4 py-2" style="font-size: 1rem;">{{ $setting['fallback']['value'] }}</td>
                            <td class="border px-4 py-2" style="font-size: 1rem;">{{ $setting['type'] }}</td>
                            <td class="border px-4 py-2" style="font-size: 1rem;">
                                <input {{ (empty($setting['is_writeable']) ? ' readonly=readonly ' : '') }} type="text" wire:model.defer="{{$model}}.{{$id}}.value" class="w-full" style="font-size: 1rem;">
                            </td>
{{--                            <td class="border px-4 py-2" style="font-size: 1rem;">{{ (empty($setting['is_writeable']) ? ' readonly=readonly ' : '') }}</td>--}}
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </form>
@else
    <p class="text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400">@lang('Protocol does not provide advanced settings or you do not have sufficient role for reading them.')</p>
@endif