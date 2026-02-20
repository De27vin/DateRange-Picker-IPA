<?php
namespace App\Traits;

use App\Models\AccountSetting;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait PasswordPolicyTrait
{
    public $settings;
    public $settingsWithKeys;
    public $settingsAccount;
    public $settingsDefault;

    private $char = "rowbxqlumphgidyfaenktvsjcz";
    private $symbols = "#@!()&^%$~/]*[{}?><;";
    private $numbers = "6203457189";

    public function getSettings(): Collection
    {
        if (empty($this->settings)) {
            $this->settings = Setting::query()
                ->where('setting_key','like','password.policy.%')->get()
                ->keyBy('setting_id');
        }

        return $this->settings;
    }

    public function generatePassword(int $accountId)
    {
        $settings = $this->getActivePasswordSettings($accountId);

        $lower = $this->char;
        $upper = str_shuffle($settings["uppercase"] ? strtoupper($this->char) : '');
        $symbols = str_shuffle($settings["symbols"] ? $this->symbols : '');
        $numbers = str_shuffle($settings["numbers"] ? $this->numbers : '');

        $settings['length'] = max($settings['length'], 4);

        do {
            $shuffle = str_shuffle($upper . $numbers . $lower . $symbols);
            $password = '';

            if ($settings['length'] <= 82)
                $password = substr($shuffle, 0, $settings['length']);

            if ($settings['length'] > 82) {
                $shuffle = str_pad($shuffle, 124, $shuffle, STR_PAD_BOTH);
                $password = substr($shuffle, 0, $settings['length']);
            }

            $atLeastOneLower = !$settings["lowercase"] || strpbrk($password, $this->char);
            $atLeastOneUpper = !$settings["uppercase"] || strpbrk($password, strtoupper($this->char));
            $atLeastOneSymbol = !$settings["symbols"] || strpbrk($password, $this->symbols);
            $atLeastOneNumber = !$settings["numbers"] || strpbrk($password, $this->numbers);

            $shouldTryAgain = !$atLeastOneLower || !$atLeastOneUpper || !$atLeastOneSymbol || !$atLeastOneNumber;

        } while ($shouldTryAgain);

        return $password;
    }

    public function getActivePasswordSettings(int $accountId)
    {
        $this->settingsWithKeys = $this->getSettingsWithKeys();
        $this->settingsAccount = $this->getSettingsAccount($accountId);
        $this->settingsDefault = $this->getSettingsDefault();

        if(count($this->settingsAccount) != count($this->settingsDefault)){
            $this->settingsAccount = $this->synchronizeAccountSettings($accountId);
        }

        if(empty($this->settingsAccount)){
            return $this->settingsDefault;
        }

        if(!toBoolean($this->settingsAccount[$this->settingsWithKeys['on']])){
            return $this->settingsDefault;
        }
        return [
            'on' => toBoolean($this->settingsAccount[$this->settingsWithKeys['on']]),
            'length' => (int)$this->settingsAccount[$this->settingsWithKeys['length']],
            'numbers' => toBoolean($this->settingsAccount[$this->settingsWithKeys['numbers']]),
            'lowercase' => toBoolean($this->settingsAccount[$this->settingsWithKeys['lowercase']]),
            'uppercase' => toBoolean($this->settingsAccount[$this->settingsWithKeys['uppercase']]),
            'symbols' => toBoolean($this->settingsAccount[$this->settingsWithKeys['symbols']])
        ];
    }

    public function synchronizeAccountSettings(int $accountId)
    {
        $settingIds = $this->getSettings()->pluck('setting_id');
        foreach ($settingIds as $setting_id) {
            if(!array_key_exists($setting_id, $this->settingsAccount)){
                $defaultValue = Setting::query()->where('setting_id','=',$setting_id)->first()->setting_value;
                DB::table('account_settings')
                    ->updateOrInsert(
                        ['as_setting_id' => $setting_id, 'as_account_id' => $accountId],
                        ['as_value' => $defaultValue]
                    );
            }
        }
        return $this->getSettingsAccount($accountId);
    }

    public function getSettingsWithKeys()
    {
        return $this->getSettings()->pluck('setting_key','setting_id')
        ->map(function($item,$key){
            return Str::after($item,'password.policy.');
        })
        ->flip()
        ->toArray();
    }

    public function getSettingsAccount(int $accountId)
    {
        $settingsById = $this->getSettings()->pluck('setting_id')->all();
        return AccountSetting::query()
            ->where('as_account_id','=',$accountId)
            ->whereIn('as_setting_id',$settingsById)
            ->pluck('as_value','as_setting_id')
            ->toArray();
    }

    public function getSettingsDefault()
    {
        return [
            'on'        => toBoolean($this->getSettings()->where("setting_key", "password.policy.symbols" )->first()['setting_value']),
            'length'    => (int)$this->getSettings()->where("setting_key", "password.policy.length" )->first()['setting_value'],
            'numbers'   => toBoolean($this->getSettings()->where("setting_key", "password.policy.numbers" )->first()['setting_value']),
            'lowercase' => toBoolean($this->getSettings()->where("setting_key", "password.policy.numbers" )->first()['setting_value']),
            'uppercase' => toBoolean($this->getSettings()->where("setting_key", "password.policy.lowercase" )->first()['setting_value']),
            'symbols'   => toBoolean($this->getSettings()->where("setting_key", "password.policy.symbols" )->first()['setting_value'])
        ];
    }
}
