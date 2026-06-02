<?php
namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AlarmNotification extends Component
{
    public $alarmCalls;

    protected $listeners = ['alarm-data-updated' => 'handleAlarmUpdate'];

    public function mount()
    {
        if(Auth::user()->isAgent){
            $this->updateAlarmCalls();
        } else {
            $this->alarmCalls = [];
        }
    }

    public function handleAlarmUpdate($data)
    {
        if (isset($data['accountId']) && $data['accountId'] == session('account.id')) {
            $this->alarmCalls = $data['alarmCalls'] ?? [];
        }
    }

    public function render()
    {
        return view('livewire.admin.alarm-notification');
    }

    public function updateAlarmCalls()
    {
//        $this->alarmCalls = $this->getAlertDevices('all', ['ALARM']);
        $this->alarmCalls = [];
    }

}
