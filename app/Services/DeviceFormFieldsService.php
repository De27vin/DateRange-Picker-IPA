<?php

namespace App\Services;

use App\Enum\ModuleFlags;
use App\Models\Module;
use App\Traits\TranslationsTrait;

class DeviceFormFieldsService
{
    use TranslationsTrait;

    private array $requiredFieldsPerModule = [];
    private array $fieldSettingsPerModule = [];

    public function getFieldsSettings(Module $module): array
    {
        $fieldsSettings = [];
        $fields = $this->getProfileData()['config']['modules'][$module->module_name]['device']['field'];

        foreach ($fields as $field => $settings) {
            $settings['required'] = $settings['required'] || $module->fieldIsRequired($field);
            $settings['visible'] = $settings['display'] || $settings['required']; // change for display later
            $settings['display'] = $settings['display'] || $settings['required'];
            $fieldsSettings[$field] = $settings;
        }

        $this->fieldSettingsPerModule[$module->module_name] = $fieldsSettings;
        return $fieldsSettings;
    }
    public function getRequiredFields(Module $module): array
    {
        $requiredFields = [];
        $fields = $this->getProfileData()['config']['modules'][$module->module_name]['device']['field'];

        foreach ($fields as $field => $settings) {
            if ($settings['required'] || $module->fieldIsRequired($field)) {
                $requiredFields[] = $field;
            }
        }

        $this->requiredFieldsPerModule[$module->module_name] = $requiredFields;

        return $requiredFields;
    }

    public function getRequiredFieldsForAllModules(): array
    {
        $modules = Module::all()->keyBy('module_name');
        foreach ($this->getProfileData()['config']['modules'] as $moduleName => $moduleSettings) {
            $requiredFields = [];
            $fields = $moduleSettings['device']['field'];

            foreach ($fields as $field => $settings) {
                if ($settings['required'] || $modules[$moduleName]->fieldIsRequired($field)) {
                    $requiredFields[] = $field;
                }
            }

            $this->requiredFieldsPerModule[$moduleName] = $requiredFields;
        }

        return $this->requiredFieldsPerModule;
    }

    public function isFieldRequired(Module $module, string $field): bool
    {
        if (!empty($this->requiredFieldsPerModule[$module->module_name])) {
            return in_array($field, $this->requiredFieldsPerModule[$module->module_name]);
        }

        return in_array($field, $this->getRequiredFields($module));
    }

    public function isFieldLocked(Module $module, string $field): bool
    {
        $locked = false;
        $profileData = $this->getProfileData();
        if (isset($profileData['config']['modules'][$module->module_name]['device']['field'][$field]['locked'])) {
            $locked = $profileData['config']['modules'][$module->module_name]['device']['field'][$field]['locked'];
        }

        $flagToCheck = match ($field) {
            'identity' => ModuleFlags::MODULE_FLAG_IDENTITY_REQUIRED,
            'module' => ModuleFlags::MODULE_FLAG_MODULE_REQUIRED,
//            'numbers' => ModuleFlags::MODULE_FLAG_NUMBER_REQUIRED,
            'pin' => ModuleFlags::MODULE_FLAG_PIN_REQUIRED,
            default => null,
        };
        $flagged = boolval($flagToCheck?->value & $module->module_flags);

        return $locked || $flagged;
    }

    public function getFlaggedFields(Module $module)
    {
        $flaggedFields = [];
        $flaggableFields = $module::fieldsFlaggable;

        foreach ($flaggableFields as $field => $flag) {
            if($module->fieldIsRequired($field)){
                $flaggedFields[] = $field;
            }
        }

        return $flaggedFields;
    }
}