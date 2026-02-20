<?php

namespace App\Http\Livewire\Ucp;

use App\Exports\CommentsExport;
use App\Traits\SearchFiltersTrait;
use Livewire\Component;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Arr;
use App\Models\DeviceSite;
use App\Models\User;
use App\Services\SearchDeviceService;
use Maatwebsite\Excel\Facades\Excel;

class ExportCommentsNew extends Component
{
    use TranslationsTrait;
    use SearchFiltersTrait;

    public $identifiers;
    public $fieldTranslations;
    public $filtersId;
    public bool $exportSites = false;
    public $locale;

    public $exportFormat = 'csv';

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
        return view('livewire.ucp.export-comments-new');
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
//                $headerLabels = Arr::add($headerLabels, $identifier, $this->fieldTranslations[$identifier] ?? __($identifier));
                $headerLabels = Arr::add($headerLabels, $identifier, ucfirst($identifier));
            }
        }

//        $headerLabels = Arr::add($headerLabels,'author', __('Author'));
//        $headerLabels = Arr::add($headerLabels,'date', __('Date'));
//        $headerLabels = Arr::add($headerLabels,'comment', __('Comment'));

        $headerLabels = Arr::add($headerLabels,'author','Author');
        $headerLabels = Arr::add($headerLabels,'date','Date');
        $headerLabels = Arr::add($headerLabels,'comment','Comment');

        return $headerLabels;
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

        public function doExportComments()
    {
        try {
            $header = $this->getCsvHeader();
            $rows = $this->generateExportRows();

            return $this->exportFormat === 'xlsx'
                ? $this->downloadExcel($rows, $header)
                : $this->downloadCsv($rows, $header);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function generateExportRows()
    {
        $result = [];
        $activeFilters = $this->getDeviceSearchFilter($this->filtersId);

        if ($this->exportSites) {
            $result = $this->getDevicesFromSites($activeFilters);
        } else {
            $result = $this->getDevices($activeFilters);
        }

        $rows = [];
        foreach ($result as $device) {
            $rows = array_merge($rows, $this->generateCsvRow($this->getCsvHeader(), $device));
        }

        return $rows;
    }

    private function getDevicesFromSites($activeFilters)
    {
        $result = [];
        $deviceSites = $this->searchService->searchDeviceSites($activeFilters);

        foreach ($deviceSites as $deviceSite) {
            if ($deviceSite->devices->count() === 0) {
                continue;
            }

            foreach ($deviceSite->devices as $device) {
                if ($this->shouldIncludeDevice($device, $activeFilters)) {
                    $result[] = $device;
                }
            }
        }

        return $result;
    }

    private function getDevices($activeFilters)
    {
        $devices = $this->searchService->searchDevices($activeFilters);

        if (empty($activeFilters['search_tabs']) || in_array('all', $activeFilters['search_tabs'])) {
            return $devices;
        }

        return $devices->filter(function($device) use ($activeFilters) {
            return $this->shouldIncludeDevice($device, $activeFilters);
        });
    }

    private function shouldIncludeDevice($device, $activeFilters)
    {
        return empty($activeFilters['search_tabs'])
            || in_array('all', $activeFilters['search_tabs'])
            || ($device->device_enabled && in_array('enabled', $activeFilters['search_tabs']))
            || (!$device->device_enabled && in_array('disabled', $activeFilters['search_tabs']));
    }

    private function downloadExcel($rows, $header)
    {
        return Excel::download(
            new CommentsExport($rows, array_values($header)),
            'device_comments_' . date('d-m-Y_H-i-s') . '.xlsx'
        );
    }

    private function downloadCsv($rows, $header)
    {
        return response()->streamDownload(function() use ($rows, $header) {
            $file = fopen('php://output', 'w+');
            fputcsv($file, $header);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, 'device_comments_' . date('d-m-Y_H-i-s') . '.csv');
    }

}