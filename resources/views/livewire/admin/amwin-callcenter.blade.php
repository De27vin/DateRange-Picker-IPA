<div class="mx-auto w-full px-4 mt-12 pb-12 px-9">

    @if($showAlarmEditForm)
        <div class="pl-1 pb-5 w-full text-sm">
            <div class="flex w-full justify-start" id="alarmboxheader">
                <h3>
                    @lang('Classification')
                </h3>
            </div>
        </div>

        <div class="pb-5 w-full text-sm mb-8 bottom-underline">
            <form wire:submit.prevent="confirmClassification">
                <div class="flex flex-col py-2 mt-2">
                    <div class=" w-full">
                        <span class="hidden lg:grid-cols-6 lg:grid-cols-7 lg:grid-cols-8 lg:grid-cols-9 lg:grid-cols-10 lg:grid-cols-11 lg:grid-cols-12"></span>
                        <fieldset>
                            <div class="grid grid-cols-2 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-{{count($activeClassifications)}} gap-x-4">
                                @foreach($commands as $command)
                                    @if(in_array($command['class_type']['ct_type'], $activeClassifications))
                                        <label
                                            wire:key="{{$command['class_type']['ct_type']}}"
                                            wire:click="changeClassification('{{$command['class_type']['ct_type']}}')"
                                            class="border border-slate-300 relative flex cursor-pointer rounded-lg  @if($currentClassification != $command['class_type']['ct_type']) bg-white opacity-60 hover:opacity-100 @else bg-orange-200 bg-opacity-70 @endif py-4 pr-2 pl-3 focus:outline-none">
                                            <input
                                                type="radio"
                                                id="{{$command['class_type']['ct_type']}}"
                                                name="classification"
                                                value="{{__($command['class_type']['ct_desc'])}}"
                                                class="sr-only"
                                                @if($currentClassification == $command['class_type']['ct_type']) checked @endif >
                                            <span class="flex flex-1 text-lg text-bold text-gray-800">{{__($command['class_type']['ct_desc'])}}</span>
                                            <svg class="-mt-1 h-5 w-5 text-red-700 @if($currentClassification != $command['class_type']['ct_type']) hidden @else _z-50 @endif " viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="pointer-events-none absolute -inset-px rounded-lg " aria-hidden="true"></span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end pr-1">
                    <x-button.primary >{{__('Confirm Selection')}}</x-button.primary>
                </div>
            </form>
        </div>
    @elseif($currentClassification)
        <div class="pl-1 pb-5 w-full text-sm">
            <div class="pl-1 flex w-full justify-start" id="alarmboxheader" style="margin-bottom: -1rem;">
                <h3>
                    @lang('Classified'): {{ __( collect($commands)->first(fn($c) => ($c['class_type']['ct_type'] === $currentClassification))['class_type']['ct_type'] ?? 'Unknown' ) }}
                </h3>
            </div>
        <div>
    @endif

    <livewire:ucp.device-details-amwin />

    <script>
        window.addEventListener('alarmClassified', event => {
            Livewire.emit('classificationReceived', event.detail.classification);
        });
    </script>
</div>