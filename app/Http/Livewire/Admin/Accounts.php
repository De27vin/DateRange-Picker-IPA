<?php
namespace App\Http\Livewire\Admin;

use App\Services\UserContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Redirect;
use App\Models\Account;
use App\Traits\AccountsTrait;

class Accounts extends Component
{
    use AccountsTrait;

    public $profile;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->userContext = app(UserContextService::class);
    }

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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

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
        $oldAccountId = session('account.id');

        $account = $this->userContext->switchAccount($id);
        $this->profile = $account->account_translation;
        $this->clearFilterSessionData();
        \Log::info('Account switched', ['user_id' => \Auth::id(), 'old_account' => $oldAccountId, 'new_account' => $id]);

        return redirect()->intended('dashboard');
    }

    /* todo: move to some search filters oriented service that takes the logic form SearchFiltersTrait.php */
    private function clearFilterSessionData()
    {
        foreach (session()->all() as $key => $value) {
            if (str_starts_with($key, 'deviceSearchFilter')) {
                session()->forget($key);
            }
        }

        session()->forget('historyFilter');
        session()->forget('severityFilter');
    }
}