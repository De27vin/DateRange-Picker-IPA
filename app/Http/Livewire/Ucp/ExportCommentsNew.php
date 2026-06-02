<?php

namespace App\Http\Livewire\Ucp;

use App\Traits\SearchFiltersTrait;
use Livewire\Component;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExportCommentsNew extends Component
{
    use TranslationsTrait;
    use SearchFiltersTrait;

    public $identifiers;
    public $fieldTranslations;
    public $filtersId;
    public bool $exportSites = false;
    public $locale;
    public string $exportComponentId;

    public $exportFormat = 'csv';

    protected $listeners = [
        'doExportComments',
    ];

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
        $this->exportComponentId = (string) Str::uuid();
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


    public function doExportComments($format = null, $delivery = 'browser')
    {
        if ($format) {
            $this->exportFormat = $format;
        }

        $downloadId = (string) Str::uuid();

        $params = [
            'filters'     => $this->getDeviceSearchFilter($this->filtersId), // resolved here — session unavailable in job
            'exportSites' => $this->exportSites,
            'identifiers' => $this->identifiers,
            'locale'      => $this->locale ?? session('locale', 'en'),
            'accountId'   => session('account.id'),
        ];

        $this->dispatchBrowserEvent('start-export', [
            'type'    => 'comments',
            'component_id' => $this->exportComponentId,
            'request' => [
                'type'        => 'comments',
                'format'      => $this->exportFormat,
                'delivery'    => $delivery,
                'params'      => $params,
                'download_id' => $downloadId,
                'component_id' => $this->exportComponentId,
            ],
        ]);
    }

}
