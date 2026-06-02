<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Module;

class ProfileAccessService
{
    private array $profileData = [];

    public function __construct(
        private readonly UserContextService $userContext
    ) {}

    public function getProfileData()
    {
        if (!empty($this->profileData)) {
            return $this->profileData;
        }

        $account = $this->userContext->getCurrentAccount();
        $this->profileData = $account->account_translation;

        return $this->profileData;
    }

    public function saveProfileData($profile)
    {
        $account = $this->userContext->getCurrentAccount();
        $account->account_translation = $profile;
        $account->save();

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
