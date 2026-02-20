<?php
namespace App\Services;

use App\Helpers\GroupCache;
use App\Models\Language;
use App\Models\Module;
use App\Models\DeviceSite;
use App\Models\AlertType;
use App\Models\AccountsModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class AccountUpdateService
{
    public $account;
    public $currentJson;
    public $templateJson;
    public $errors;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function synchronizeAccountJsonSchema()
    {
        $this->templateJson = json_decode(file_get_contents(config_path('account_template.json')), true);
        $this->currentJson = $this->account->account_translation ?? [];

        // synchronize base structure
        $this->currentJson = $this->recursivelyMergeJsonFromTemplate($this->templateJson, $this->currentJson);

        // synchronize available languages
        $this->synchronizeLanguagesData();

        // synchronize translations
        $this->synchronizeTranslationsData();

        $this->synchronizeAlertsData();
        $this->synchronizeModulesData();
        //$this->checkAccountModuleData();

        // sorting of all json alphabetically
        $this->currentJson = $this->recursivelySortKeys($this->currentJson);

        $this->account->account_translation = $this->currentJson;
        $this->account->save();
        GroupCache::forgetGroup('profile_data');
    }

    public function synchronizeLanguagesData()
    {
        $dbLanguages = Language::get()->pluck('language_enabled','language_code')->toArray();
        ksort($dbLanguages);
        $jsonLanguages = $this->currentJson['languages'];
        ksort($jsonLanguages);
        if(md5(json_encode($dbLanguages)) != md5(json_encode($jsonLanguages))){
            $this->updateLanguagesData($jsonLanguages, $dbLanguages);
        }
    }

    public function synchronizeTranslationsData()
    {
        foreach ($this->currentJson['languages'] as $langCode => $active) {
            $this->currentJson['translations'][$langCode] = $this->recursivelyMergeJsonFromTemplate(
                $this->currentJson['translations']['default'],
                $this->currentJson['translations'][$langCode] ?? []
            );
        }

        // removing redundant fields in field translations
        foreach ($this->currentJson['translations'] as $langCode => $langTranslations) {
            foreach ($langTranslations['device']['field'] as $field => $translation) {
                if (!isset($this->templateJson['translations']['default']['device']['field'][$field])) {
                    unset($this->currentJson['translations'][$langCode]['device']['field'][$field]);
                }
            }
        }
    }

    public function synchronizeAlertsData()
    {
        $dbAlertTypes = AlertType::get()->pluck('at_desc', 'at_type')->toArray();
        $jsonAlertVisibilityConfig = array_keys($this->currentJson['config']['alert']['display']);
        $jsonAlertCriticalityConfig = array_keys($this->currentJson['config']['alert']['critical']);
        $jsonAlertAlarmalityConfig = array_keys($this->currentJson['config']['alert']['alarm']);
        $jsonAlertTypesTranslations = array_keys($this->currentJson['translations']['default']['alert']['type']);

        $dbVsVisibilityConfig = count(array_diff_key($dbAlertTypes, array_flip($jsonAlertVisibilityConfig)));
        $dbVsCriticalityConfig = count(array_diff_key($dbAlertTypes, array_flip($jsonAlertCriticalityConfig)));
        $dbVsAlarmalityConfig = count(array_diff_key($dbAlertTypes, array_flip($jsonAlertAlarmalityConfig)));
        $dbVsTranslations = count(array_diff_key($dbAlertTypes, array_flip($jsonAlertTypesTranslations)));

        if ($dbVsVisibilityConfig || $dbVsCriticalityConfig || $dbVsTranslations || $dbVsAlarmalityConfig) {
            $this->updateAlertTypesData($dbAlertTypes);
        }
    }

    public function synchronizeModulesData()
    {
        // adding lacking fields in modules
        foreach ($this->templateJson['config']['modules']['SYSTEM']['device']['field'] as $field => $fieldSettings) {
            foreach ($this->currentJson['config']['modules'] as $module => $moduleSettings) {
                if (!isset($moduleSettings['device']['field'][$field])) {
                    $this->currentJson['config']['modules'][$module]['device']['field'][$field] = $fieldSettings;
                }
            }
        }

        // removing redundant fields in modules
        foreach ($this->currentJson['config']['modules'] as $module => $moduleSettings) {
            foreach ($moduleSettings['device']['field'] as $field => $fieldSettings) {
                if (!isset($this->templateJson['config']['modules']['SYSTEM']['device']['field'][$field])) {
                    unset($this->currentJson['config']['modules'][$module]['device']['field'][$field]);
                }
            }
        }

        // getting actual modules from DB
        $dbModulesKeyedObj = Module::all()->keyBy('module_name');

        // removing non-existing in DB module entries from json
        foreach ($this->currentJson['config']['modules'] as $module => $moduleSettings) {
            if (empty($dbModulesKeyedObj[$module])) {
                unset($this->currentJson['config']['modules'][$module]);
            }
        }

        // adding non-existing in JSON modules from DB
        $dbModulesKeyedArr = $dbModulesKeyedObj->toArray();
        $jsonModules = array_keys($this->currentJson['config']['modules']);
          if (count(array_diff_key($dbModulesKeyedArr, array_flip($jsonModules))) > 0) {
            $this->updateModulesData(array_flip($jsonModules), $dbModulesKeyedArr);
        }

        // updating locked/required fields in settings
        foreach ($this->currentJson['config']['modules'] as $module => $moduleSettings) {
            $moduleObj = $dbModulesKeyedObj[$module];
            foreach ($moduleSettings['device']['field'] as $field => $fieldSettings) {

                $this->currentJson['config']['modules'][$module]['device']['field'][$field]['locked'] = false;
                if (array_key_exists($field, Module::fieldsFlaggable)) {
                    if ($field === 'numbers') {
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['required'] = $moduleObj->fieldIsRequired($field);
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['display'] = $moduleObj->fieldIsRequired($field);
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['locked'] = $moduleObj->fieldIsRequired($field);
                    }
                    elseif ($moduleObj->fieldIsRequired($field)) {
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['required'] = true;
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['display'] = true;
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['locked'] = true;
                    } else {
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['required'] = false;
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['display'] = false;
                        $this->currentJson['config']['modules'][$module]['device']['field'][$field]['locked'] = true;
                    }
                }

                if ($field === 'equipment') {
                    $this->currentJson['config']['modules'][$module]['device']['field'][$field]['required'] = true;
                    $this->currentJson['config']['modules'][$module]['device']['field'][$field]['display'] = true;
                    $this->currentJson['config']['modules'][$module]['device']['field'][$field]['locked'] = true;
                }

            }
        }
    }

    public function checkAccountModuleData()
    {
        $usedAccountModules = DeviceSite::query()->where('ds_account_id','=',$this->account->account_id)->get()->unique('ds_protocol_id')->pluck('ds_protocol_id')->toArray();
        $attachedModules = $this->account->modules->pluck('module_id')->toArray();
        $modulesNotInSync = array_diff_key(array_flip($usedAccountModules), array_flip($attachedModules));
        if(count($modulesNotInSync)){
            $this->updateAccountModuleData($modulesNotInSync);
        }
    }

    public function updateLanguagesData($jsonLanguages, $dbLanguages)
    {
        // updates are only from DB to JSON
        // no JSON data deleted
        try{
            $langNotInJSON = array_diff_key($dbLanguages, $jsonLanguages);
            $langNotInDB = array_diff_key($jsonLanguages, $dbLanguages);

            foreach ($langNotInJSON as $language => $state) {
                $this->currentJson['languages'][$language] = (bool) $state;
                $this->currentJson['translations'][$language] = $this->currentJson['translations']['default'];
            }

            foreach ($langNotInDB as $language => $state) {
                unset($this->currentJson['languages'][$language]);
                unset($this->currentJson['translations'][$language]);
            }

        } catch(\Exception $e){
            $this->errors[] = $e->getMessage();
        }
    }

    public function updateAlertTypesData($dbAlertTypes)
    {
        // updates are only from DB to JSON
        // no JSON data deleted
        try{
            foreach ($dbAlertTypes as $alertType => $description) {
                if (empty($this->currentJson['config']['alert']['display'][$alertType])) {
                    $this->currentJson['config']['alert']['display'][$alertType] = true;
                }
                if (empty($this->currentJson['config']['alert']['critical'][$alertType])) {
                    $severity = AlertType::where('at_type', $alertType)->first()->alert_severity->as_type;
                    $this->currentJson['config']['alert']['critical'][$alertType] = ($severity === 'MAJOR');
                }
                if (empty($this->currentJson['config']['alert']['alarm'][$alertType])) {
                    $this->currentJson['config']['alert']['alarm'][$alertType] = ($alertType === 'ALARM');
                }
                if (empty($this->currentJson['translations']['default']['alert']['type'][$alertType])) {
                    $this->currentJson['translations']['default']['alert']['type'][$alertType] = $description;
                }
                foreach ($this->currentJson['languages'] as $language => $state) {
                    if (empty($this->currentJson['translations'][$language]['alert']['type'][$alertType])) {
                        $this->currentJson['translations'][$language]['alert']['type'][$alertType] = $description;
                    }
                }
            }
        } catch(\Exception $e){
            $this->errors[] = $e->getMessage();
        }
    }

    public function updateModulesData($jsonModules, $dbModulesKeyedArr)
    {
        // updates are only from DB to JSON
        // no JSON data deleted
        try{
            $modulesNotInJSON = array_diff_key($dbModulesKeyedArr, $jsonModules);
            foreach ($modulesNotInJSON as $module => $id) {
                $this->currentJson['config']['modules'][$module] = $this->templateJson['config']['modules']['SYSTEM'];
            }
        } catch(\Exception $e){
            $this->errors[] = $e->getMessage();
        }

    }

    // if many-to-many relation data is missing, all modules used for device-sites of account are attached to current account
    public function updateAccountModuleData($modulesNotInSync)
    {
        foreach ($modulesNotInSync as $moduleId => $dummy) {
            $module = Module::query()->where('module_id','=',$moduleId)->first();
            if($module->module_flags != 0){
                DB::table('accounts_modules')->insert([
                    'am_account_id' => $this->account->account_id,
                    'am_module_id' => $moduleId
                ]);
            }
        }
    }

    // todo: consider passing by reference for speed optimization
    private function recursivelyMergeJsonFromTemplate($templateJson, $targetJson)
    {
        $targetJson = $targetJson ?? [];
        foreach ($templateJson as $key => $value) {
            if (array_key_exists($key, $targetJson)) {
                if (is_array($value) && is_array($targetJson[$key])) {
                    $targetJson[$key] = $this->recursivelyMergeJsonFromTemplate($value, $targetJson[$key]);
                }
            } else {
                $targetJson[$key] = $value;
            }
        }

        return $targetJson;
    }

    // todo: consider passing by reference for speed optimization
    private function recursivelySortKeys($array)
    {
        ksort($array);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->recursivelySortKeys($value);
            }
        }
        return $array;
    }

    private function recursivelySortKeysRef(&$array) {
        ksort($array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursivelySortKeysRef($value);
            }
        }
    }
}