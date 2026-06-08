<div class="mt-4 mx-auto w-full px-12 pb-5 pt-8 font-medium">
    <div class="w-full">

        @php
            $tabs = [
                'languages' => __('Languages'),
                'fieldTranslations' => __('Translations'),
                'settings' => __('Settings'),
                'customFields' => __('Custom Fields'),
            ];

            if (Auth::user()->isAdmin) {
                $tabs['passwordPolicy'] = __('Password Policy');
                $tabs['dashboard'] = __('Dashboard');
                $tabs['charts'] = __('Charts');
            }
        @endphp

        <x-page.tabs-primary
                defaultTab="languages"
                :tabs="$tabs"
                :barMargin="'4.5rem'"
        >
            <x-slot name="languagesSlot"><livewire:settings.languages /></x-slot>
            <x-slot name="fieldTranslationsSlot"><livewire:settings.fields-translations /></x-slot>
            @if(Auth::user()->isAdmin)
                <x-slot name="passwordPolicySlot"><livewire:admin.password-policy /></x-slot>
                <x-slot name="dashboardSlot">
                    <x-account.dashboard-widget-settings />
                </x-slot>
                <x-slot name="chartsSlot">
                    <x-account.dashboard-widget-settings mode="charts" />
                </x-slot>
            @endif
            <x-slot name="settingsSlot">
                <x-account.account-device-settings :accountSettings="$accountSettings" />
            </x-slot>
            <x-slot name="customFieldsSlot"><livewire:settings.custom-fields /></x-slot>
        </x-page.tabs-primary>

    </div>

</div>
