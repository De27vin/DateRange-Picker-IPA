<div class="pb-12 mx-auto px-4">

    <x-page.header class="mt-8 mb-2 h-20">
        <x-slot name="title">@lang('Custom Fields')</x-slot>
        <x-slot name="description">@lang('Add additional custom fields for device sites or devicess. Each section tab must be saved separately.')</x-slot>
    </x-page.header>



    <x-page.tabs-secondary
            defaultTab="siteFields"
            :tabs="[
                'siteFields' => __('Site custom fields'),
                'deviceFields' => __('Device custom fields')
            ]">
        <x-slot name="siteFieldsSlot">
            <x-account.account-custom-fields
                    :currentCustomFields="$currentCustomFieldsSite"
                    :newCustomFields="$newCustomFieldsSite"
                    :iconModalTarget="$iconModalTarget"
                    :deleteModalTarget="$deleteModalTarget"
                    :languages="$languages"
                    :icons="$icons"
                    :target="'Site'"
            ></x-account.account-custom-fields>
        </x-slot>
        <x-slot name="deviceFieldsSlot">
            <x-account.account-custom-fields
                    :currentCustomFields="$currentCustomFieldsDevice"
                    :newCustomFields="$newCustomFieldsDevice"
                    :iconModalTarget="$iconModalTarget"
                    :deleteModalTarget="$deleteModalTarget"
                    :languages="$languages"
                    :icons="$icons"
                    :target="'Device'"
            ></x-account.account-custom-fields>
        </x-slot>
    </x-page.tabs-secondary>

</div>