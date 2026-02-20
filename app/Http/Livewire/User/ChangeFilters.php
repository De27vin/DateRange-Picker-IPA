<?php

namespace App\Http\Livewire\User;

use App\Enum\DashboardTabs;
use App\Enum\EquipmentTabs;
use App\Helpers\GroupCache;
use App\Http\Livewire\Filters\EquipmentFilters;
use App\Services\SearchTabsService;
use Illuminate\Support\Facades\Auth;
use App\Traits\TranslationsTrait;
use Livewire\Component;

class ChangeFilters extends Component
{
    use TranslationsTrait;

    public array $defaultDashboardFilters;
    public array $defaultEquipmentFilters;

    private SearchTabsService $searchTabsService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->searchTabsService = new SearchTabsService();
    }

    public function mount()
    {
        if (!session('account.id') || !Auth::check()) {
            \Log::error('Required data not found in ChangeFilters component');
            abort(500);
        }

        $this->defaultDashboardFilters = $this->searchTabsService->getUserDashboardTabs();
        $this->defaultEquipmentFilters = $this->searchTabsService->getUserEquipmentTabs();
    }

    public function render()
    {
        return view('livewire.user.change-filters');
    }

    public function updateDashboardFilter($filter)
    {
        $userId = (string) Auth::user()->user_id;

        $profile = $this->getProfileData();
        $profile['default_tabs'][$userId]['dashboard'] = $filter;
        $this->saveProfileData($profile);
        GroupCache::forgetGroup('profile_data');

        $this->defaultDashboardFilters = $this->searchTabsService->getUserDashboardTabs();
        $this->notify('success', __('Default dashboard tab updated'));
    }

    public function updateEquipmentFilter($filter)
    {
        $userId = (string) Auth::user()->user_id;

        $profile = $this->getProfileData();
        $profile['default_tabs'][$userId]['equipment'] = $filter;
        $this->saveProfileData($profile);
        GroupCache::forgetGroup('profile_data');

        $this->defaultEquipmentFilters = $this->searchTabsService->getUserEquipmentTabs();
        $this->notify('success', __('Default equipment tab updated'));
    }

}