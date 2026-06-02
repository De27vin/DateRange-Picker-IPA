<?php

namespace App\Http\Livewire\Filters;

use App\Services\DeviceAlertsService;
use App\Services\SearchTabsService;
use Livewire\Component;
use App\Models\DeviceLabelOld;
use App\Traits\TranslationsTrait;
use App\Traits\SearchFiltersTrait;

/**
 * @deprecated
 */
class EquipmentFilters extends Component
{
    use SearchFiltersTrait;
    use TranslationsTrait;

    public $locale;
    public $accountId;
    public $alertTranslations;
    public $fieldTranslations;
    public $alertsCountGrouped;
    public $alertsGrouped;

    // search/filter functionality
    public $groups;
    public $filters;
    public $sortOptions = [];
    public $searchOptions = [];
    public $searchSelected = ['all'];
    public $searchTabs = [];

    public $filtersId = 'Equipment';

    protected $listeners = [
        'hideCreateForm',
        'updateFilters',
        'render',
        'getPhoneData'
    ];

    private DeviceAlertsService $alertsService;
    private SearchTabsService $searchTabsService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->alertsService = new DeviceAlertsService();
        $this->searchTabsService = new SearchTabsService();
    }

    public function mount()
    {
        $this->locale = session('locale', 'en');
        $this->accountId = session('account.id');
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
        $this->alertTranslations = $this->getAlertTranslations($this->locale);
        $this->alertsGrouped = $this->alertsService->getAlertsGrouping();
        $this->alertsCountGrouped = $this->alertsService->getGroupedAlertsCounts(session('account.id'));

        // search/filter functionality
        $this->searchTabs = $this->searchTabsService->getUserEquipmentTabs();
        $this->sortOptions = $this->getSortOptions();
        $this->searchOptions = $this->getSearchOptions();
        $this->filters = $this->getDeviceSearchFilter($this->filtersId);
        $this->filters['sortedby'] = 'ds_name';
        $this->filters['sortDirection'] = 'asc';
        $this->filters['alerts'] = $this->getAlertsForFilters($this->filtersId);
        $this->filters['search_tabs'] = array_keys(array_filter($this->searchTabs));
        $this->searchSelected = $this->filters['search_selected'];
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
//        $this->groups = DeviceLabel::query()
//            ->with('device_label_settings')
//            ->where('dl_account_id', '=', $this->accountId)
//            ->withDepth()
//            ->defaultOrder()
//            ->get()
//            ->toTree()
//            ->toArray();
    }

    public function hydrate()
    {
        $this->emit('equipment_filters_updated');
    }

    public function render()
    {
        return view('livewire.filters.equipment-filters');
    }

    public function toggleSearchTab($tab)
    {
        foreach ($this->searchTabs as $filter => $active) {
            $this->searchTabs[$filter] = false;
        }
        $this->searchTabs[$tab] = true;



        $this->filters['search_tabs'] = array_keys(array_filter($this->searchTabs));
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
    }

    public function toggleFilterAlert($type)
    {
        if (isset($this->filters['alerts'][$type])) {
            $this->filters['alerts'][$type] = !$this->filters['alerts'][$type];
        }

        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
    }

    public function updatedFilters()
    {
        $this->updateDeviceSearchFilter($this->filters, $this->filtersId);
    }

    public function resetFilters()
    {
        $this->searchTabs = $this->searchTabsService->getUserEquipmentTabs();
        $this->filters['sortedby'] = 'ds_name';
        $this->filters['sortDirection'] = 'asc';
        $this->filters['alerts'] = array_fill_keys(array_keys(array_filter($this->getAlertTypeDisplayStates())), false);
        $this->filters['groups'] = [];
        $this->filters['search'] = '';
        $this->filters['search_tabs'] = array_keys(array_filter($this->searchTabs));
        $this->searchSelected = $this->filters['search_selected'] = ['all'];
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
            'ds_name' => trans('Site name'),
            'ds_modified' => trans('Modified'),
            'ds_created' => trans('Created'),
        ]);
    }
}