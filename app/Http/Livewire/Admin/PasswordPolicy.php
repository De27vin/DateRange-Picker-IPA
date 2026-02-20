<?php
namespace App\Http\Livewire\Admin;

use App\Helpers\GroupCache;
use App\Traits\PasswordPolicyTrait;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PasswordPolicy extends Component
{
    use PasswordPolicyTrait;

    public $passwordPolicies;
    public $showEditModal;

    public $comments = [
        'on' => 'Use your password policy',
        'length' => 'Minimal password length',
        'uppercase' => 'Password should contain at least one uppercase letter',
        'lowercase' => 'Password should contain at least one lowercase letter',
        'numbers' => 'Password should contain at least one number',
        'symbols' => 'Password should contain at least one symbol',
    ];

    public function mount()
    {
        $this->accountId = session('account.id');
        $this->passwordPolicies = $this->getActivePasswordSettings($this->accountId);
    }

    public function render()
    {
        return view('livewire.admin.password-policy');
    }

    public function togglePasswordPolicy($key)
    {
        $this->passwordPolicies[$key] = !$this->passwordPolicies[$key];
        if($key == 'on'){
            DB::table('account_settings')
                ->updateOrInsert(
                    ['as_setting_id' => $this->settingsWithKeys['on'], 'as_account_id' => $this->accountId],
                    ['as_value' => $this->passwordPolicies['on']]
                );
            $this->passwordPolicies = $this->getActivePasswordSettings($this->accountId);
        }
        GroupCache::forgetGroup('settings');
    }

    public function updatePasswordPolicy()
    {
        if($this->passwordPolicies['on']){
            $this->settingsWithKeys = $this->getSettingsWithKeys();
            foreach ($this->settingsWithKeys as $key => $value) {
                DB::table('account_settings')
                    ->updateOrInsert(
                        ['as_setting_id' => $value, 'as_account_id' => $this->accountId],
                        ['as_value' => $this->passwordPolicies[$key]]
                    );
            }
        } else {
            DB::table('account_settings')
                ->updateOrInsert(
                    ['as_setting_id' => $this->settingsWithKeys['on'], 'as_account_id' => $this->accountId],
                    ['as_value' => $this->passwordPolicies['on']]
                );
        }
        $this->passwordPolicies = $this->getActivePasswordSettings($this->accountId);
        $this->showPolicyModal = false;
        GroupCache::forgetGroup('settings');
        $this->notify('Settings for password policy updated');
    }
}