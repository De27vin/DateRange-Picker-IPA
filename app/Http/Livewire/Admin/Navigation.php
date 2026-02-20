<?php
namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Redirect;
use App\Traits\AccountsTrait;
use Illuminate\Support\Facades\Auth;

class Navigation extends Component
{
    use AccountsTrait;

    public $scope;
    public $account;
    public $locale;
    public $profile;
    public $languages;
    public $theme;
    public $activeAccount;

    public function mount()
    {
        if(session('account.id') != null){
            $this->activeAccount = session('account.id');
            // $this->languages = $this->profile['languages'];
            $this->theme = $this->profile['theme'];
            $this->account = $this->getCurrentAccount();
        } else {
            $this->activeAccount = null;
        }
        if(Auth::user()){
            $this->accounts = $this->getUserAccounts();
        }
    }

    public function render()
    {
        return view('livewire.admin.navigation');
    }


    public function updateDeviceSearch()
    {
        $this->filters = $this->storeFilter('deviceSearchFilter', $this->filters);
        $this->emit('switch', 'ucp.devices');
    }


}