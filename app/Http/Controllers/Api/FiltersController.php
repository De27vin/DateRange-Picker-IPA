<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GroupCache;
use App\Models\DeviceLabelGroup;
use App\Models\Number;
use App\Services\CustomFieldsService;
use App\Services\DeviceAlertsService;
use App\Services\DeviceFormFieldsService;
use App\Services\SearchTabsService;
use App\Services\SettingsService;
use App\Traits\DeviceFormTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use App\Models\DeviceGateway;

class FiltersController extends BaseController
{
    use SearchFiltersTrait {
        getSearchOptions as getSearchOptionsFromTrait;
    }
    use TranslationsTrait;
//    use DeviceFormTrait;

    public function __construct(
        private readonly SearchTabsService $searchTabsService,
        private readonly DeviceAlertsService $deviceAlertsService,
//        private readonly CustomFieldsService     $customFieldsService,
//        private readonly SettingsService         $settingsService,
//        private readonly DeviceFormFieldsService $formFieldsService,
    ) {}

    public function getDashboardSearchTabs()
    {
        $this->checks();
        return $this->searchTabsService->getUserDashboardTabs();
    }

    public function getFilters()
    {
        $this->checks();
        $filtersId = request()->get('filtersId');
        $filters = $this->getDeviceSearchFilter($filtersId);
        $filters['alerts'] = $this->getAlertsForFilters($filtersId);

        return $filters;
    }

    public function getGroupedAlertsCounts()
    {
        $this->checks();
        return $this->deviceAlertsService->getGroupedAlertsCounts(session('account.id'));
    }

    public function getAlertsTranslations()
    {
        $this->checks();
        return $this->getAlertTranslations(session('locale', 'en'));
    }

    public function getSearchOptions()
    {
        $this->checks();
        return $this->getSearchOptionsFromTrait();
    }

    public function getEquipmentSearchTabs()
    {
        $this->checks();
        return $this->searchTabsService->getUserEquipmentTabs();
    }

    private function checks()
    {
        if (empty(session('account.id'))) {
            \Log::error('empty account id in ' . __FILE__ . ' ' . __METHOD__);
            abort(500);
        }
    }
}
