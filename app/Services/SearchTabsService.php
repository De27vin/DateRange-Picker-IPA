<?php
namespace App\Services;


use App\Enum\DashboardTabs;
use App\Enum\EquipmentTabs;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Facades\Auth;

class SearchTabsService
{
    use TranslationsTrait;


    public function getUserDashboardTabs(): array
    {
        $userId = (string) Auth::user()->user_id;
        $profile = $this->getProfileData();

        if (isset($profile['default_tabs'][$userId]['dashboard'])) {
            $defaultTab = $profile['default_tabs'][$userId]['dashboard'];
            $tabs = array_fill_keys(DashboardTabs::values(), false);
            $tabs[$defaultTab] = true;
        } else {
            $tabs = $this->getDefaultDashboardTabs();
        }

        return $tabs;
    }

    public function getDefaultDashboardTabs(): array
    {
        $agent = Auth::user()->isAgent;
        $mobile = Auth::user()->isMobile;
        $tabs = array_fill_keys(DashboardTabs::values(), false);

        return match (true) {
            $agent && $mobile, $agent => array_merge($tabs, [DashboardTabs::ALARMS->value => true]),
            $mobile => array_merge($tabs, [DashboardTabs::OVERDUES->value => true]),
            default => array_merge($tabs, [DashboardTabs::ALL->value => true]),
        };
    }

    public function getUserEquipmentTabs(): array
    {
        $userId = (string) Auth::user()->user_id;
        $profile = $this->getProfileData();

        if (isset($profile['default_tabs'][$userId]['equipment'])) {
            $defaultTab = $profile['default_tabs'][$userId]['equipment'];
            $tabs = array_fill_keys(EquipmentTabs::values(), false);
            $tabs[$defaultTab] = true;
        } else {
            $tabs = $this->getDefaultEquipmentTabs();
        }

        return $tabs;
    }

    public function getDefaultEquipmentTabs(): array
    {
        $tabs = array_fill_keys(EquipmentTabs::values(), false);
        $tabs[EquipmentTabs::ENABLED->value] = true;

        return $tabs;
    }
}