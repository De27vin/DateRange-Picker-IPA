<div
    x-data="{ value: @entangle($attributes->wire('model')), picker: undefined }"
    x-init="
        moment.locale('en-en');
        new Pikaday({
            field: $refs.input,
            firstDay: 1,
            i18n: {
                months:         moment.localeData()._months,
                weekdays:       moment.localeData()._weekdays,
                weekdaysShort:  moment.localeData()._weekdaysShort
            },
            format: 'DD.MM.YYYY',
            onOpen() { this.setDate(moment($refs.input.value,'DD.MM.YYYY').toDate() ) }
        })"
    x-on:change="value = $event.target.value"
    class="flex "
>
    <input
        type="text"
        {{ $attributes->whereDoesntStartWith('wire:model') }}
        x-ref="input"
        x-bind:value="value"
        class="daterangepicker text-normal cursor-default"
    />
</div>
