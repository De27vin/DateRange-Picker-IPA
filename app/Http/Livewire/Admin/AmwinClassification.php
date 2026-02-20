<?php

namespace App\Http\Livewire\Admin;

use App\Models\Command;
use App\Models\Device;
use App\Models\Account;
use App\Models\Session;
use App\Models\DeviceSite;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Traits\FreeswitchApiTrait;
use App\Traits\AccountsTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\DevicesTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Facades\Redirect;

class AmwinClassification extends Component
{
    use FreeswitchApiTrait;
    use AccountsTrait;
    use DeviceFormTrait;
    use DevicesTrait;
    use SearchFiltersTrait;
    use TranslationsTrait;

    public $commands;
    public $currentClassification;
    public $activeClassifications;
    public $showAlarmEditForm;
    public $alarm;
    public $deviceSites;
    public $voiceAlertId;
    public $deviceId;
    public $deviceEQID;

    public $alertTranslations;
    public $fieldTranslations;

    protected $listeners = ['classificationReceived'];

    public function mount($device_equipment)
    {
        $this->deviceEQID = $device_equipment;
        $this->locale = session('locale', 'en');

        $amwinAccountId = Account::where('account_slug', 'liftcare')->first()?->account_id;
        if (!$amwinAccountId) {
            \Log::error('AMWIN-CLASSIFICATION - amwin-liftcare account does not exists');
            abort(404);
        }

        $userBelongsToAmwinAccount = Auth::user()->roles()->where('ur_account_id', $amwinAccountId)->exists();
        if (!$userBelongsToAmwinAccount) {
            \Log::error('AMWIN-CLASSIFICATION - user does not belong to amwin-liftcare account');
            abort(403);
        }

        $device = Device::with('device_alerts')->where('device_equipment', $this->deviceEQID)->first();
        $voiceAlert = $device?->device_alerts?->first(fn($da) => $da->alert_type->at_type ===  'VOICE');
        if (empty($voiceAlert)) {
            \Log::warning('REQUESTED ACCESS TO AMWIN-CLASSIFICATION ROUTE ON INVALID CIRCUMSTANCES');
            $this->notify('error', __('You cannot enter this page'));
            abort(403);
        }

        if (empty(Auth::user()->isAgent) || empty($device)) {
            $this->showAlarmEditForm = false;
            $this->deviceSites['deviceSite'] = [];
        }

        $this->deviceId = $device->device_id;
        $this->activeClassifications = ['ALARM_ACTIVE', 'ALARM_NO_SOUND', 'ALARM_MISUSE', 'ALARM_END', 'TEST_CALL_OK', 'TEST_CALL_NOK', 'NO_ALARM'];
        $this->currentClassification = 'ALARM_ACTIVE';
        $this->commands = [];
        $this->alarm = [];
        $this->showAlarmEditForm = true;
        $this->getCommands();

        $this->alertTranslations = $this->getAlertTranslations($this->locale);
        $this->fieldTranslations = $this->getFieldTranslations($this->locale);

        $deviceSite = $this->getDeviceDetails($device->device_id);
        if ($deviceSite->count() > 0) {
            $deviceSite = $deviceSite->toArray();
            $this->deviceSites['deviceSite'] = $deviceSite[0];
            $this->deviceStates = $this->getDeviceStates()->toArray()[$device->device_id];
        } else {
            $this->showAlarmEditForm = false;
            $this->deviceSites['deviceSite'] = [];

        }
    }

    public function render()
    {
        if ($this->deviceSites != null) {
            return view('livewire.admin.amwin-callcenter',[
                'deviceAlerts'     => $this->updateDeviceAlerts()
            ]);
        } else {
            return view('livewire.admin.amwin-callcenter',[
                'deviceAlerts'     => []
            ]);
        }
    }

    public function classificationReceived($classification)
    {
        $this->currentClassification = $classification;
        $this->showAlarmEditForm = false;
    }

    public function changeClassification($type)
    {
        $this->currentClassification = $type;
    }

    public function confirmClassification($type = null)
    {
        if(!Auth::user()->isAgent){
            $this->notify('error', __('You have no permission to classify active alarm calls'));
            return false;
        }
        try{
            $device = Device::with('device_alerts')->findOrFail($this->deviceId);
            $voiceAlert = $device->device_alerts->first(fn($da) => $da->alert_type->at_type ===  'VOICE');
            $voiceSession = $voiceAlert?->session;

        } catch(\Throwable $e){
            \Log::error($e, ['Caught']);
            $this->notify('error', __('No session uuid for this call found'));
            $voiceSession = null;
        } finally {
            $this->resetAlarmEditForm();
        }


        $uuid = null;
        $host = null;
        if ($voiceSession == null) {
            $this->notify('error', __('UUID for this alarm not found'));
        } else {
            $uuid = $voiceSession->session_uuid;
            $host = $voiceSession->session_host;
        }

        if ($uuid != null) {
            try{
                if ($this->fsMake('ucp classify call ' . $uuid . ' ' . $this->currentClassification, false, false, $host)) {
                    \Log::debug('ucp classify call ' . $uuid . ' ' . $this->currentClassification);
                    $this->notify('success', __('ucp CLASSIFICATION command processed'));
                } else {
                    $this->notify('error', __('ucp CLASSIFICATION command failed'));
                }
            } catch (\Throwable $e) {
                \Log::error($e, ['Caught']);
                $this->notify('error', __('Error occurred on fs call'));
            } finally {
                $this->resetAlarmEditForm();
            }

        } else  {
            $this->resetAlarmEditForm();
        }
    }

    public function getCommands()
    {
        $this->commands = Command::with('class_type')->classification()->get()->toArray();
    }

    public function resetAlarmEditForm()
    {
        session(['alarm'   => ['deviceId' => null, 'show' => false]]); // this is rather to delete
        $this->showAlarmEditForm = false;
        $this->emit('updateDataForAlarmEditForm'); // this is rather to delete
    }
}