<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Module;

class ProfileAccessService
{
    private array $profileData = [];

    public function getProfileData()
    {
        if (!empty($this->profileData)) {
            return $this->profileData;
        }

        $updatedAccount = Account::query()->where('account_id', session('account.id'))->first();
        $profile = $updatedAccount->account_translation;

        return $profile;
    }

    public function saveProfileData($profile)
    {
        $updatedAccount = Account::query()->where('account_id','=',session('account.id'))->first();
        $updatedAccount->account_translation = $profile;
        $updatedAccount->save();

        $this->profileData = $profile;
    }

    public function isFieldRequired(Module $module, string $field): bool
    {
        return $this->getProfileData()['config']['modules'][$module->module_name]['device']['field'][$field]['required'];
    }

    public function getAlertTypeDisplayStates()
    {
        return $this->getProfileData()['config']['alert']['display'] ?? [];
    }

    public function getAlertCriticalityStates()
    {
        return $this->getProfileData()['config']['alert']['critical'] ?? [];
    }

    public function getAlertAlarmalityStates()
    {
        return $this->getProfileData()['config']['alert']['alarm'] ?? [];
    }

    private function arrayAccessor($path, $array)
    {
        foreach($path as $key) {
            $array = $array ? ($array[$key] ?? null) : null;
        }
        return $array;
    }

}
