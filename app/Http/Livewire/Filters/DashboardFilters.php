<?php
namespace App\Http\Livewire\Filters;

use App\Enum\DashboardTabs;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\DeviceLabelOld;
use App\Services\DeviceAlertsService;
use App\Services\SearchTabsService;
use App\Traits\AccountsTrait;
use App\Traits\DevicesTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @deprecated
 */
class DashboardFilters extends Component
{
    use WithPerPagePagination;
    use SearchFiltersTrait;
    use TranslationsTrait;
    use AccountsTrait;
    use DevicesTrait;

    public $export;
    public $alerts;
    public $alertsCountGrouped;
    public $fieldTranslations;
    public $alertTranslations;


    // search/filter functionality
    public $groups;
    public $filters;
    public $sortOptions = [];
    public $searchOptions = [];
    public $searchSelected = ['all'];
    public $searchTabs = [];
    public $filtersId = 'Dashboard';

    private DeviceAlertsService $alertsService;
    private SearchTabsService $searchTabsService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->alertsService = new DeviceAlertsService();
        $this->searchTabsService = new SearchTabsService();
    }


    public function mount()
    {
        // search/filter functionality
        $this->searchTabs = $this->searchTabsService->getUserDashboardTabs();
        $this->sortOptions = $this->getSortOptions();
        $this->searchOptions = $this->getSearchOptions();
        $this->filters = $this->getDeviceSearchFilter($this->filtersId);
        $this->filters['sortedby'] = 'device_equipment';
        $this->filters['sortDirection'] = 'asc';
        $this->filters['alerts'] = $this->getAlertsForFilters($this->filtersId);
        $this->searchSelected = $this->filters['search_selected'];
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
        $this->perPage = 20;
//        $this->groups = DeviceLabel::query()
//            ->with('device_label_settings')
//            ->where('dl_account_id', '=', session('account.id'))
//            ->withDepth()
//            ->defaultOrder()
//            ->get()
//            ->toTree()
//            ->toArray();


        $this->setAlertFiltersByActiveTabs();
        $this->initAlertData();
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);

        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
        $this->alertTranslations = $this->getAlertTranslations($this->locale);
    }

    public function hydrate()
    {
        $this->emit('dashboard_filters_updated');
    }

    public function initAlertData()
    {
        $this->alerts = $this->alertsService->getAlertsGrouping();
        $this->alertsCount = $this->alertsService->getAllAlertCounts(session('account.id'));
        $this->alertsCountGrouped = $this->alertsService->getGroupedAlertsCounts(session('account.id'));

        $this->alertFilters = array_fill_keys($this->alerts['visible'], false);
        $this->alertFilters['PERIODICAL'] = true;
    }


    public function render()
    {
        $translatedTabs = collect($this->searchTabs)->mapWithKeys(function ($active, $tab) {
            return [$tab => __($tab)];
        })->toArray();

        return view('livewire.filters.dashboard-filters', [
            'translatedTabs' => $translatedTabs
        ]);
    }

    public function toggleSearchTab($tab)
    {
        foreach ($this->searchTabs as $searchTab => $active) {
            $this->searchTabs[$searchTab] = ($searchTab === $tab);
        }
        $this->setAlertFiltersByActiveTabs();
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
        $this->emit('doUpdateTabs', $this->searchTabs);
    }

    private function setAlertFiltersByActiveTabs()
    {
        $alarmality = array_keys(array_filter($this->getAlertAlarmalityStates()));

        foreach ($this->filters['alerts'] as $alert => $active) {
            if (in_array($alert, $alarmality)) {
                $this->filters['alerts'][$alert] = $this->searchTabs['alarms'];
            }
        }

        $this->filters['alerts']['PERIODICAL'] = $this->searchTabs['overdues'];

        foreach ($this->filters['alerts'] as $alert => $active) {
            if (!in_array($alert, array_merge($alarmality, ['PERIODICAL']))) {
                $this->filters['alerts'][$alert] = $this->searchTabs['alerts'];
            }
        }
    }

    public function toggleFilterAlert($type)
    {
        $alarmality = array_filter($this->getAlertAlarmalityStates());
        $alertality = array_diff_key($this->filters['alerts'], array_merge($alarmality, ['PERIODICAL' => true]));
        $alertality = array_map(fn() => true, $alertality);

        if (isset($this->filters['alerts'][$type])) {
            $this->filters['alerts'][$type] = !$this->filters['alerts'][$type];

            $allAlarmsSelected = (count($alarmality) === count(array_intersect_assoc($alarmality, $this->filters['alerts'])));
            $noAlarmsSelected = (0 === count(array_intersect_assoc($alarmality, $this->filters['alerts'])));

            $allAlertsSelected = (count($alertality) === count(array_intersect_assoc($alertality, $this->filters['alerts'])));
            $noAlertsSelected = (0 === count(array_intersect_assoc($alertality, $this->filters['alerts'])));

            $this->searchTabs['overdues'] = $this->filters['alerts']['PERIODICAL'];

            if ($allAlarmsSelected) $this->searchTabs['alarms'] = true;
            if ($noAlarmsSelected) $this->searchTabs['alarms'] = false;

            if ($allAlertsSelected) $this->searchTabs['alerts'] = true;
            if ($noAlertsSelected) $this->searchTabs['alerts'] = false;

            $this->searchTabs['all'] = !count(array_filter($this->filters['alerts']));
        }

        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
    }

    public function updatedFilters()
    {
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
    }

    public function resetFilters()
    {
        $this->filters['sortedby'] = 'device_equipment';
        $this->filters['sortDirection'] = 'asc';
        $this->filters['alerts'] = array_fill_keys(array_keys(array_filter($this->getAlertTypeDisplayStates())), false);
        $this->filters['groups'] = [];
        $this->filters['search'] = '';
        $this->searchSelected = $this->filters['search_selected'] = ['all'];
        $this->searchTabs = $this->searchTabsService->getUserDashboardTabs();

        $this->setAlertFiltersByActiveTabs();

        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
    }

    public function attachGroup($id)
    {
        if (empty($this->filters['groups'][$id])) {
            $this->filters['groups'][$id] = DeviceLabelOld::where('dl_id', '=', $id)->first()->toArray();
            $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
        }
    }

    public function detachGroup($id)
    {
        if (!empty($this->filters['groups'][$id])) {
            unset($this->filters['groups'][$id]);
            $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
        }
    }

    public function updatedSearchSelected(array $searchSelected): void
    {
        $this->searchSelected = $searchSelected;
        $this->filters['search_selected'] = $searchSelected;

        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);

        $this->emit('doUpdateSelectedSearch', $searchSelected);
    }

    private function getSortOptions(): string
    {
        return json_encode([
            'device_equipment' => trans('Equipment'),
            'device_identity' => trans('Identity'),
            'device_modified' => trans('Modified'),
            'device_created' => trans('Created'),
        ]);
    }
}