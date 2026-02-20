<?php

namespace App\Http\Livewire\BasfLink;

use App\Services\SessionHistoryService;
use Livewire\Component;
use App\Models\Device;
use App\Models\Session;
use App\Models\DeviceAlert;
use App\Models\DeviceComment as Comment;
use App\Traits\FreeswitchApiTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\Event;
use App\Models\SessionType;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DeviceDetails extends Component
{
    /** THIS IS PORTING TO TEST */

    use FreeswitchApiTrait;
    use DeviceFormTrait;
    use SearchFiltersTrait;
    use WithSorting;
    use WithBulkActions;
    use WithPerPagePagination;
    use WithCachedRows;
    use TranslationsTrait;

    public $device;
    public $deviceStates;
    public $deviceTasks;
    public $monitorActiveTask;
    public $locale;
    public $hasSet;
    public $hasRevival;
    public $hasCarcall;
    public $translation;

    public $latestComment;

    public $actionButtons = [
//        '_carcall' => false,
//        '_revival' => false,
//        '_set' => false,
        '_trigger' => false
    ];

    protected $listeners = [];

    // ---------------------
    // PORTING TO TEST BELOW
    // ---------------------

    public $fieldTranslations;
    public $alertTranslations;

    private SessionHistoryService $sessionHistoryService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->sessionHistoryService = new SessionHistoryService();
    }

    public function mount()
    {
        session(['currentPage' => 'device-details']);
        $this->locale = session('locale', 'en');
        $this->alertTranslations = $this->getAlertTranslations($this->locale);
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);
        $this->device = $this->getDeviceFromUrl();
        $this->updateActionButtons();
        if($this->device != null){
            $this->initDeviceData();

            $this->deviceId = $this->device->device_id;
            $this->pin      = $this->device->device_pin;
            $this->setpin   = $this->device->device_setpin;
            $this->updateLatestComment();
            $this->deviceTasks = [
                'carcall' => false,
                'set'     => false,
                'revival' => false,
                'export'  => false
            ];

        } else {
            abort(404);
        }
    }


    public function render()
    {
        return view('livewire.basf_link.device-details', [
            'deviceAlerts' => $this->updateDeviceAlertsByDeviceId($this->device->device_id)
        ])->layout('layouts.basf_link');
    }

    private function updateActionButtons()
    {
        $this->moduleFunctions = $this->device->device_site->module?->funktions->pluck('function_call')->all() ?? [];
        foreach ($this->actionButtons as $key => $value) {
            if (in_array($key, $this->moduleFunctions)) {
                $this->actionButtons[$key] = true;
            }
        }
    }

    private function updateLatestComment()
    {
        $latestComment = Comment::query()->where('dc_device_id', '=', $this->device->device_id)->orderByDesc('dc_created')->first();
        if($latestComment != null){
            $this->latestComment = $latestComment->dc_text;
        } else {
            $this->latestComment = '';
        }
    }

    private function updateDeviceDetails($deviceId)
    {
        $this->device = Device::withTrashed()->where('device_id','=',$deviceId)->first();
        $this->deviceId = $this->device->device_id;
        $this->updateDeviceStats();
    }

    private function initDeviceData()
    {
        $this->deviceAlerts = DeviceAlert::with('alert_type')
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->da_device_id => $item];
            });
        if($number = $this->device->device_site->numbers()->first()){
            $this->device['device_number_primary'] = $number->number_value;
        } else {
            $this->device['device_number_primary'] = null;
        }
        $this->deviceStates       = json_decode(json_encode($this->device->states), true);
        $this->countries = \Countries::lookup(session()->get('locale','en'), true)->flip()->toArray();
        $this->prepareDeviceFormData();

        $this->hasAddress = false;

        if($this->device->device_site->address == null){
            $this->makeEmptyAddress();
            $this->hasAddress = false;
        } else {
            $this->address = $this->device->device_site->address;
            $this->location = $this->device->device_site->address->location; // \App\Models\Location::query()->where('location_id','=',$this->address->address_location_id)->first();
            $this->hasAddress = true;
        }
        $this->getPhoneData();
        $this->locale = Auth::user()->locale?->language?->language_code ?? session('locale', 'en');
    }

    /***
     * Livewire::Events
     *
     * */
    private function updateDeviceStats()
    {
        $this->deviceStates = json_decode(json_encode($this->device->states), true);
        $this->deviceAlerts = DeviceAlert::with('alert_type')
            ->get()
            ->mapToGroups(function($item, $key){
                return [$item->da_device_id => $item];
            });
        $this->updateLatestComment();
        if($this->device->device_setpin == null){
            $this->emit('updatePin');
        }
    }

//    private function removeAlert($deviceId, $alertType, $value = null)
//    {
//        $cmd = 'ucp clear device '.$deviceId.' '.strtoupper($alertType);
//        if ($value) {
//            $cmd = $cmd.' '.$value;
//        }
//
//        if($result = $this->fsMake($cmd, false, true)) {
//            $this->notify('Chosen alert successfuly deleted.');
//        } else {
//            $this->notify('ucp reload command failed');
//        }
//        $this->updateDeviceStats();
//    }

    private function updatedDateFilter()
    {
        $this->dateFilter = $this->storeFilter('dateFilter', $this->dateFilter);
    }

    public function makeFSCall($action)
    {
        switch ($action) {
//            case 'carcall':
//                $this->makeFsCarcall();
//                break;
            case 'trigger':
                $this->makeFsTrigger();
                break;
//            case 'revival':
//                $this->makeFsRevival();
//                break;
//            case 'set':
//                $this->makeFsSet();
//                break;

            default:
                // code...
                break;
        }
    }

//    private function makeFsCarcall()
//    {
//        if($this->fsMake('ucp carcall device ' . $this->device->device_id . ' ' . Auth::user()->user_ext)) {
//            $this->notify('success', 'executing CARCALL command successful');
//            $this->monitorActiveTask = true;
//            $this->updateHistory();
//        } else {
//            $this->notify('error', 'executing CARCALL command failed');
//            $this->notify('error', trans('Connection problems'));
//        }
//        $this->updateDeviceStats();
//    }

    private function makeFsTrigger()
    {
        if($result = $this->fsMake('ucp trigger device ' . $this->device->device_id)) {
            $this->notify('success','executing TRIGGER command successful');
//            $this->updateLatestSessionHistoryVisibility();
            $this->render();
        } else {
            $this->notify('error','executing TRIGGER command failed');
            $this->notify('error',trans('Connection problems'));
        }
    }

//    private function makeFsSet()
//    {
//        if($result = $this->fsMake('ucp set device ' . $this->device->device_id)) {
//            $this->notify('success','executing SET command successful');
//            $this->updateLatestSessionHistoryVisibility();
//            $this->render();
//        } else {
//            $this->notify('error','executing SET command failed');
//            $this->notify('error',trans('Connection problems'));
//        }
//    }

//    private function makeFsRevival()
//    {
//        if($result = $this->fsMake('ucp revive device ' . $this->device->device_id)) {
//            $this->notify('success','executing REVIVAL command successful');
//            $this->updateLatestSessionHistoryVisibility();
//            $this->render();
//        } else {
//            $this->notify('error','executing REVIVAL command failed');
//            $this->notify('error',trans('Connection problems'));
//        }
//        $this->updateDeviceStats();
//    }

    private function getCarcallAvailability()
    {
        $state = false;
        if(Auth::user()->isAgent && Auth::user()->user_ext != null){
            $state = true;
        }
        $this->hasCarcall = $state;
    }

    private function getSetAvailability()
    {
        $state = false;
        $deviceFunctions = $this->device->device_site->module->funktions ?? [];
        foreach ($deviceFunctions as $item) {
            if($item->function_call == '_set'){
                $state = true;
            }
        }
        $this->hasSet = $state;
    }

    private function getRevivalAvailability()
    {
        $state = false;
        $deviceFunctions = $this->device->device_site->module->funktions ?? [];
        foreach ($deviceFunctions as $item) {
            if($item->function_call == '_revival'){
                $state = true;
            }
        }
        $this->hasRevival = $state;
    }

}
