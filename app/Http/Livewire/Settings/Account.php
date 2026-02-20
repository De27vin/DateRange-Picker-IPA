<?php
namespace App\Http\Livewire\Settings;

use App\Helpers\GroupCache;
use App\Services\RolesService;
use App\Services\SettingsService;
use App\Traits\FreeswitchApiTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Account extends Component
{
    use FreeswitchApiTrait;
    use TranslationsTrait;
    public $accountSettings;
    private SettingsService $settingsService;
    private RolesService $rolesService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->settingsService = new SettingsService();
        $this->rolesService = new RolesService();
    }

    public function mount()
    {
        if (empty(session('account.id'))) {
            \Log::error('Access to account settings without account id in session');
            abort(404);
        }

        $accountSettings = $this->settingsService->getAccountSettings(session('account.id'));
        $this->accountSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACCOUNT, $accountSettings);
    }

    public function render()
    {
        return view('livewire.settings.account');
    }

    public function updateSettings()
    {
        $updated = $this->settingsService->updateAccountSettings(
            session('account.id'),
            collect($this->accountSettings),
        );
        if ($updated) {
            $this->notify('success', __('Settings for account devices updated'));
            $this->makeFsReload();
            GroupCache::forgetGroup('settings');
        } else {
            $this->notify('error', __('Error occurred on settings update'));
        }

        $accountSettings = $this->settingsService->getAccountSettings(session('account.id'));
        $this->accountSettings = $this->settingsService->prepareSettingsForView(SettingsService::ACCOUNT, $accountSettings);
    }

    public function changeBoolSetting($key, $value)
    {
        if (!empty($this->accountSettings[$key]['bool']) && !empty($this->accountSettings[$key]['is_writeable'])) {
            $this->accountSettings[$key]['bool'] = [
                'on' => 'on' === $value && !$this->accountSettings[$key]['bool']['on'],
                'off' => 'off' === $value && !$this->accountSettings[$key]['bool']['off'],
                'na' => 'na' === $value && !$this->accountSettings[$key]['bool']['na'],
            ];
            if ($this->accountSettings[$key]['bool']['on']) {
                $this->accountSettings[$key]['value'] = '1';
                $this->accountSettings[$key]['not_applicable'] = false;
            }
            elseif ($this->accountSettings[$key]['bool']['off']) {
                $this->accountSettings[$key]['value'] = '0';
                $this->accountSettings[$key]['not_applicable'] = false;
            }
            elseif ($this->accountSettings[$key]['bool']['na']) {
                $this->accountSettings[$key]['value'] = '';
                $this->accountSettings[$key]['not_applicable'] = true;
            }
            else {
                $this->accountSettings[$key]['value'] = '';
                $this->accountSettings[$key]['not_applicable'] = false;
            }
        }
    }

    public function changeSettingNa($settingId, bool $state)
    {
        if (!empty($this->accountSettings[$settingId])) {
            $this->accountSettings[$settingId]['not_applicable'] = $state;
        }
    }

    public function cancelSettings()
    {
        $this->mount();
    }

    private function makeFsReload()
    {
        if($result = $this->fsMake('ucp del account ' . session('account.id'), false, true)) {
            $this->notify('success', __('ucp reload account command processed'));
        } else {
            $this->notify('error', __('Due to connection problems, it is possible that the changed values will only take effect after a slight delay.'));
        }
    }
}