<?php

namespace App\Http\Livewire\Ucp;

use App\Traits\SearchFiltersTrait;
use Livewire\Component;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Arr;
use App\Models\DeviceSite;
use App\Models\User;
use App\Services\SearchDeviceService;

/**
 * @deprecated
 */
class ExportComments extends Component
{
    use TranslationsTrait;
    use SearchFiltersTrait;

    public $identifiers;
    public $fieldTranslations;
    public $filtersId;
    public bool $exportSites = false;
    public $locale;

    protected $listeners = [
        'doExportComments',
    ];

    private SearchDeviceService $searchService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->searchService = new SearchDeviceService();
    }

    public function mount(string $filtersId, bool $exportSites = false)
    {
        $this->filtersId = $filtersId;
        $this->exportSites = $exportSites;

        $this->locale = session('locale', 'en');
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
        $this->identifiers = [
            'equipment' => false, 
            'identity' => false,
            'pin' => false,
            'module' => false,
            'numbers' => false,
            'site' => false,
        ];
    }

    public function render()
    {
        return view('livewire.ucp.export-comments');
    }

    public function toggleIdentifier($item)
    {
        $this->identifiers[$item] = !$this->identifiers[$item];
    }

    public function getCsvHeader()
    {
        $headerLabels['device_id'] = trans('Device ID');
        foreach($this->identifiers as $identifier => $state){
            if($state){
                $headerLabels = Arr::add($headerLabels, $identifier, $this->fieldTranslations[$identifier] ?? __($identifier));
            }
        }
        $headerLabels = Arr::add($headerLabels,'author', __('Author'));
        $headerLabels = Arr::add($headerLabels,'date', __('Date'));
        $headerLabels = Arr::add($headerLabels,'comment', __('Comment'));
        return $headerLabels;
    }

    public function doExportComments()
    {
        return response()->streamDownload(function() {
            $file = fopen('php://output', 'w+');
            $header = $this->getCsvHeader();
            fputcsv($file, $header);

            $result = [];
            $activeFilters = $this->getDeviceSearchFilter($this->filtersId);
            if ($this->exportSites) {
                $deviceSites = $this->searchService->searchDeviceSites($activeFilters);

                foreach ($deviceSites as $id => $deviceSite) {
                    $devices = $deviceSites[$id]->devices;

                    if ($devices->count() === 0) {
                        continue;
                    }

                    if (empty($activeFilters['search_tabs'] || in_array('all', $activeFilters['search_tabs']))) {
                        foreach ($devices as $device) {
                            $result = array_merge($result, [$device]);
                        }
                        continue;
                    }

                    foreach ($devices as $device) {
                        if ($device->device_enabled) {
                            if (in_array('enabled', $activeFilters['search_tabs'])) {
                                $result = array_merge($result, [$device]);
                            }
                        }
                        if (!$device->device_enabled) {
                            if (in_array('disabled', $activeFilters['search_tabs'])) {
                                $result = array_merge($result, [$device]);
                            }
                        }
                    }
                }
            } else {
                $devices = $this->searchService->searchDevices($activeFilters);

                if (empty($activeFilters['search_tabs'] || in_array('all', $activeFilters['search_tabs']))) {
                    $result = $devices;
                } else {
                    foreach ($devices as $device) {
                        if ($device->device_enabled) {
                            if (in_array('enabled', $activeFilters['search_tabs'])) {
                                $result = array_merge($result, [$device]);
                            }
                        }
                        if (!$device->device_enabled) {
                            if (in_array('disabled', $activeFilters['search_tabs'])) {
                                $result = array_merge($result, [$device]);
                            }
                        }
                    }
                }
            }

            foreach ($result as $device) {
                $rows = $this->generateCsvRow($header, $device);
                foreach($rows as $oneLine){
                    fputcsv($file, $oneLine);
                }
            }

            fclose($file);
        }, 'device_comments_'.date('d-m-Y H:i:s').'.csv');

    }

    public function generateCsvRow($header,$device)
    {
        $rows = [];
        foreach ($device['device_comments'] as $commentItem) {
            $index = 0;
            $row = [];
            foreach ($header as $key => $value) {
                if($key == 'device_id'){
                    $row[$index] = $device['device_id'];
                }
                if($key == 'equipment'){
                    $row[$index] = $device['device_equipment'];
                }
                if($key == 'identity'){
                    $row[$index] = $device['device_identity'];
                }
                if($key == 'pin'){
                    $row[$index] = $device['device_pin'];
                }
                if($key == 'module'){
                    $row[$index] = $device['device_module'];
                }
                if($key == 'numbers'){
                    $row[$index] = implode('|', $device['device_site']['numbers']->pluck('number_value')->toArray());
                }
                if($key == 'site'){
                    $row[$index] = $device['device_site']['ds_name'];
                }

                if($key == 'author'){
                    $user = User::where('user_id','=',$commentItem['dc_user_id'])->first();
                    if($user != null){
                        $author = $user->name;
                    } else {
                        $author = '';
                    }
                    $row[$index] = $author;
                }
                if($key == 'date'){
                    $row[$index] = toUserDateTime($commentItem['dc_created']);
                }
                if($key == 'comment'){
                    $row[$index] = $commentItem['dc_text'];
                }
                $index = $index+1;
            }
            $rows[] = $row;
        }
        return $rows;
    }


}