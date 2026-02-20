<?php
namespace App\Http\Livewire\Admin;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Redirect;
use App\Models\Account;
use App\Traits\AccountsTrait;

class Accounts extends Component
{
    use AccountsTrait;

    public $profile;

//    public function __construct()
//    {
//        parent::__construct();
//        DB::connection()->enableQueryLog();
//    }

//    public function __destruct()
//    {
//        $queries = DB::getQueryLog();
//        $totalTime = array_sum(array_column($queries, 'time'));
//        \Log::debug('Query debug in Admin\Accounts', [$totalTime, $queries]);
//    }

    public function mount()
    {
        $accounts = $this->getUserAccounts();
        foreach ($accounts as $account) {
            $this->accounts[$account->account_id]['name'] = $account->account_name;
            $this->accounts[$account->account_id]['slug'] = $account->account_slug;
            $this->accounts[$account->account_id]['theme'] = $account->account_translation['theme'];
        }
        $this->account = $this->getCurrentAccount();
    }

    public function render()
    {
        return view('livewire.admin.accounts');
    }

    public function setAccount($id)
    {
        $this->setAccountSessionData($id);
        Cookie::queue('ucp_account', $id, 1000000);
        return redirect()->intended('dashboard');
    }
}