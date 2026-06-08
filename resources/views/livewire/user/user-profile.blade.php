<div class="mt-4 mx-auto w-full pt-4 px-12 font-medium">
    <div class="w-full">

        <x-page.header class="h-16">
            <x-slot name="title">@lang('User Profile')</x-slot>
            <x-slot name="description">@lang('Manage your user settings')</x-slot>
        </x-page.header>

        <x-page.tabs-primary
                defaultTab="{{ session('tab', 'changePassword') }}"
                :tabs="[
            'changePassword' => __('Password'),
            'changeLanguage' => __('Language'),
            'changeFilters' => __('Filters'),
            'changeDashboard' => __('Dashboard'),
            'changeCharts' => __('Charts'),
        ]">
            <x-slot name="changePasswordSlot"><div style="margin-inline: 1.5rem;"><livewire:user.change-password/></div></x-slot>
            <x-slot name="changeLanguageSlot"><div style="margin-inline: 1.5rem;"><livewire:user.change-language/></div></x-slot>
            <x-slot name="changeFiltersSlot"><div style="margin-inline: 1.5rem;"><livewire:user.change-filters/></div></x-slot>
            <x-slot name="changeDashboardSlot"><div style="margin-inline: 1.5rem;"><livewire:user.change-dashboard key="change-dashboard"/></div></x-slot>
            <x-slot name="changeChartsSlot"><div style="margin-inline: 1.5rem;"><livewire:user.change-dashboard scope="charts" key="change-charts"/></div></x-slot>
        </x-page.tabs-primary>

    </div>
</div>
