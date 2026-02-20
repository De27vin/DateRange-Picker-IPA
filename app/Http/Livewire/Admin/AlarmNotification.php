<?php
namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Traits\DevicesTrait;
use App\Traits\AccountsTrait;
use Illuminate\Support\Facades\Auth;

class AlarmNotification extends Component
{
    use DevicesTrait;
    use AccountsTrait;

    public $alarmCalls;


    public function mount()
    {
        if(Auth::user()->isAgent){
            $this->updateAlarmCalls();
        } else {
            $this->alarmCalls = [];
        }
    }

    public function render()
    {
        return view('livewire.admin.alarm-notification');
    }

    public function updateAlarmCalls()
    {
        $this->alarmCalls = $this->getAlertDevices('all', ['VOICE']);
    }

    // public function takeAlarmCall($id)
    // {
    //     session(['alarm'   => ['deviceId' => $id, 'show' => true]]);
    //     // $this->emit('switch','admin.alarm');
    //     // return redirect()->route('device-details',['deviceId' => $id]);
    // }

}