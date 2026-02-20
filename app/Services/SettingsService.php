<?php
namespace App\Services;

use App\Models\AccountModuleSetting;
use App\Models\AccountSetting;
use App\Models\Device;
use App\Models\DeviceLabel;
use App\Models\DeviceLabelSetting;
use App\Models\DeviceLabelSite;
use App\Models\DeviceSetting;
use App\Models\DeviceSite;
use App\Models\DeviceSiteSetting;
use App\Models\HostSetting;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\ModulesSettable;
use App\Models\Setting;
use App\Traits\TranslationsTrait;
use App\Traits\TrimInputs;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as StdCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SettingsService
{
    use TranslationsTrait;
    use TrimInputs;
    private Collection $settings;
    private RolesService $rolesService;

    const DEVICE = 'device';
    const DEVICE_SITE = 'device_site';
    const LABEL = 'label';
    const ACC_MOD = 'account_module';
    const MODULE = 'module';
    const ACCOUNT = 'account';
    const HOST = 'host';
    const SETTING = 'setting';

    public function __construct()
    {
        $this->rolesService = new RolesService();
    }

    private array $functionsMap = [
            'device' => 'getDeviceSettings',
            'device_site' => 'getDeviceSiteSettings',
            'label' => 'getLabelSettings',
            'account_module' => 'getAccountModuleSettings',
            'module' => 'getModuleSettings',
            'account' => 'getAccountSettings',
            'host' => 'getHostSettings',
            'setting' => 'getSettings',
    ];

    public function getSettingsOrder(?string $after = null): array
    {
        $order = [
            'device',
            'device_site',
            'label',
            'account_module',
            'module',
            'account',
            'host',
            'setting',
        ];

        return empty($after) ? $order : array_slice($order, array_search($after, $order) + 1);
    }

    public function prepareSettingsForView(string $level, Collection $settings, ?Collection $settables = null): array
    {
        $translations = $this->getSettingTranslations(session('locale', 'default'), 'device');

        if ($settables) {
            $settings = $this->applySettables($settings, $settables);
        }
        $settings = $this->removePrefixedSettings($settings, 'password');

        $viewSettings = [];
        foreach ($settings as $setting) {

            if (!$this->rolesService->doesUserHaveHigherOrEqualRole(Auth::user(), $setting->setting_read_role_id, session('account.id'))) {
                continue;
            }

            $translationKey = Str::replace('.', '_', $setting->setting_key);
            $viewSettings[$setting->setting_id]['key'] = $setting->setting_key;
            $viewSettings[$setting->setting_id]['setting_id'] = $setting->setting_id;
            $viewSettings[$setting->setting_id]['translation'] = $translations[$translationKey] ?? $this->getSettingDefaultTranslation($translationKey);
            $viewSettings[$setting->setting_id]['type'] = $setting->setting_type->st_type;
            $viewSettings[$setting->setting_id]['value'] = $setting->{$level.'_value'} ?? '';
            $viewSettings[$setting->setting_id]['is_writeable'] = $this->rolesService->doesUserHaveHigherOrEqualRole(Auth::user(), $setting->setting_write_role_id, session('account.id'));
            $viewSettings[$setting->setting_id]['not_applicable'] = isset($setting->{$level.'_value'}) && $setting->{$level.'_value'} === '';

            if ($setting->setting_type->st_type === 'bool') {
                $viewSettings[$setting->setting_id]['bool'] = [
                    'on' => isset($setting->{$level.'_value'}) && $setting->{$level.'_value'} === '1',
                    'off' => isset($setting->{$level.'_value'}) && $setting->{$level.'_value'} === '0',
                    'na' => isset($setting->{$level.'_value'}) && $setting->{$level.'_value'} === '',
                ];
            }

            foreach ($this->getSettingsOrder($level) as $fallback) {
                if (isset($setting->{$fallback.'_value'})) {

                    $fallbackLabel = ($fallback === 'setting') ? 'root' : $fallback;
                    $fallbackLabel = __('Fallback:').' '.Str::replace('_', ' ', $fallbackLabel);

                    // Add label source information if it's a label fallback
                    if ($fallback === 'label' && isset($setting->label_source)) {
                        $fallbackLabel .= " ({$setting->label_source})";
                    }


                    $viewSettings[$setting->setting_id]['fallback'] = [
                        'label' => $fallbackLabel,
                        'value' => $setting->{$fallback.'_value'},
                    ];
                    break;
                }
            }
        }

        return $viewSettings;
    }


    public function getSettings(): Collection
    {
        if (empty($this->settings)) {
            $this->settings = Setting::with('setting_type')->get();
        }

        return $this->settings;
    }

    public function getHostSettings(int $hostId): Collection
    {
        $settings = $this->getSettings();
        $hostSettings = HostSetting::where('hs_host_id', $hostId)->get();

        $settings = $settings->each(function ($setting) use ($hostSettings) {
            $setting->host_value = $hostSettings->firstWhere('hs_setting_id', $setting->setting_id)?->hs_value;
        });

        return $settings;
    }

    public function getAccountSettings(int $accountId, ?int $hostId = null): Collection
    {
        $settings = $hostId ? $this->getHostSettings($hostId) : $this->getSettings();
        $accountSettings = AccountSetting::where('as_account_id', $accountId)->get();

        $settings = $settings->each(function ($setting) use ($accountSettings) {
            $setting->account_value = $accountSettings->firstWhere('as_setting_id', $setting->setting_id)?->as_value;
        });

        return $settings;
    }

    public function getModuleSettings(int $moduleId, ?int $accountId = null, ?int $hostId = null): Collection
    {
        $settings = $this->getAccountSettings($accountId, $hostId);
        $moduleSettings = ModuleSetting::where('ms_module_id', $moduleId)->get();

        $settings = $settings->each(function ($setting) use ($moduleSettings) {
            $setting->module_value = $moduleSettings->firstWhere('ms_setting_id', $setting->setting_id)?->ms_value;
        });

        return $settings;
    }

    public function getAccountModuleSettings(int $moduleId, int $accountId, ?int $hostId = null): Collection
    {
        $settings = $this->getModuleSettings($moduleId, $accountId, $hostId);
        $accountModuleSettings = AccountModuleSetting::where([
            ['ams_module_id', $moduleId],
            ['ams_account_id', $accountId],
        ])->get();

        $settings = $settings->each(function ($setting) use ($accountModuleSettings) {
            $setting->account_module_value = $accountModuleSettings->firstWhere('ams_setting_id', $setting->setting_id)?->ams_value;
        });

        return $settings;
    }

    public function getLabelSettings(DeviceLabel $label): Collection
    {
        $settings = $this->getAccountSettings($label->dl_account_id);
        $labelSettings = DeviceLabelSetting::where('dls_dl_id', $label->dl_id)->get();

        $settings = $settings->each(function ($setting) use ($labelSettings) {
            $setting->label_value = $labelSettings->firstWhere('dls_setting_id', $setting->setting_id)?->dls_value;
        });

        return $settings;
    }

    public function getDeviceSiteLabelsSettings(int $deviceSiteId): array
    {
        $labelSettings = DeviceLabelSetting::with(['device_label.group'])
            ->whereHas('device_label.device_sites', function($query) use ($deviceSiteId) {
                $query->where('dld_ds_id', $deviceSiteId);
            })
            ->get()
            ->groupBy('dls_setting_id')
            ->map(function($group) {
                $sorted = $group->sortBy([
                    fn($a, $b) => $a->device_label->group?->dlg_order <=> $b->device_label->group?->dlg_order,
                    fn($a, $b) => $a->device_label->dl_order <=> $b->device_label->dl_order
                ]);

                $lastSetting = $sorted->first();
                return [
                    'value' => $lastSetting->dls_value,
                    'label_info' => $lastSetting->device_label->group
                        ? "{$lastSetting->device_label->group->dlg_name} -> {$lastSetting->device_label->dl_name}"
                        : $lastSetting->device_label->dl_name
                ];
            })->toArray();

        return $labelSettings;
    }


    public function getDeviceSiteSettings(DeviceSite $deviceSite, ?int $hostId = null): Collection
    {
        $settings = $this->getAccountModuleSettings($deviceSite->ds_protocol_id, $deviceSite->ds_account_id, $hostId);
        $labelsSettings = $this->getDeviceSiteLabelsSettings($deviceSite->ds_id);
        $deviceSiteSettings = DeviceSiteSetting::where('dss_ds_id', $deviceSite->ds_id)->get();

        $settings = $settings->each(function ($setting) use ($labelsSettings, $deviceSiteSettings) {
            $labelSetting = $labelsSettings[$setting->setting_id] ?? null;
            $setting->label_value = $labelSetting ? $labelSetting['value'] : null;
            $setting->label_source = $labelSetting ? $labelSetting['label_info'] : null;
            $setting->device_site_value = $deviceSiteSettings->firstWhere('dss_setting_id', $setting->setting_id)?->dss_value;
        });

        return $settings;
    }

    public function getDeviceSettings(Device $device, ?int $hostId = null): Collection
    {
        $settings = $this->getDeviceSiteSettings($device->device_site, $hostId);
        $deviceSettings = DeviceSetting::where('ds_device_id', $device->device_id)->get();

        $settings = $settings->each(function ($setting) use ($deviceSettings) {
            $setting->device_value = $deviceSettings->firstWhere('ds_setting_id', $setting->setting_id)?->ds_value;
        });

        return $settings;
    }

    public function updateDeviceSettings(Device $device, StdCollection $updateSettings, ?bool $useSettables = null): bool
    {
        $updateSettings = $this->trimStringsInCollectionRecursively($updateSettings);

        if (!is_null($useSettables)) {
            if ($useSettables) {
                $updateSettings = $this->applySettables($updateSettings, $device->device_site->module->modules_settables);
            } else {
                $updateSettings = $this->removeSettables($updateSettings, $device->device_site->module->modules_settables);
            }
        }

        try {
            $currentDeviceSettings = $this->getDeviceSettings($device)->keyBy('setting_id');
            foreach ($updateSettings as $setting) {
                $currentSetting = $currentDeviceSettings[$setting['setting_id']];

                if (!empty($setting['not_applicable']) && $this->isSettingEmpty($setting['value'])) {
                    DeviceSetting::updateOrCreate(
                        ['ds_setting_id' => $currentSetting->setting_id, 'ds_device_id' => $device->device_id],
                        ['ds_value' => ''],
                    );
                    continue;
                }
                if (isset($currentSetting->device_value) && $this->isSettingEmpty($setting['value'])) {
                    DeviceSetting::where([
                        ['ds_setting_id', $currentSetting->setting_id],
                        ['ds_device_id', $device->device_id],
                    ])->delete();
                    continue;
                }

                if (!isset($currentSetting->device_value) && $this->isSettingEmpty($setting['value'])) continue;
                if (isset($currentSetting->device_value) && $currentSetting->device_value === $setting['value']) continue;

                if (isset($currentSetting->device_value) && $currentSetting->device_value !== $setting['value']) {
                    DeviceSetting::where([
                        ['ds_setting_id', $currentSetting->setting_id],
                        ['ds_device_id', $device->device_id],
                    ])->update(['ds_value' => $setting['value']]);
                    continue;
                }
                if (!isset($currentSetting->device_value) && !$this->isSettingEmpty($setting['value'])) {
                    DeviceSetting::create([
                        'ds_device_id' => $device->device_id,
                        'ds_setting_id' => $currentSetting->setting_id,
                        'ds_value' => $setting['value'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
        return true;
    }

    public function updateDeviceSiteSettings(DeviceSite $deviceSite, StdCollection $updateSettings, ?bool $useSettables = null): bool
    {
        $updateSettings = $this->trimStringsInCollectionRecursively($updateSettings);

        if (!is_null($useSettables)) {
            if ($useSettables) {
                $updateSettings = $this->applySettables($updateSettings, $deviceSite->module->modules_settables);
            } else {
                $updateSettings = $this->removeSettables($updateSettings, $deviceSite->module->modules_settables);
            }
        }

        try {
            $currentDeviceSiteSettings = $this->getDeviceSiteSettings($deviceSite)->keyBy('setting_id');
            foreach ($updateSettings as $setting) {
                $currentSetting = $currentDeviceSiteSettings[$setting['setting_id']];

                if (!empty($setting['not_applicable']) && $this->isSettingEmpty($setting['value'])) {
                    DeviceSiteSetting::updateOrCreate(
                        ['dss_setting_id' => $currentSetting->setting_id, 'dss_ds_id' => $deviceSite->ds_id],
                        ['dss_value' => ''],
                    );
                    continue;
                }
                if (isset($currentSetting->device_site_value) && $this->isSettingEmpty($setting['value'])) {
                    DeviceSiteSetting::where([
                        ['dss_setting_id', $currentSetting->setting_id],
                        ['dss_ds_id', $deviceSite->ds_id],
                    ])->delete();
                    continue;
                }

                if (!isset($currentSetting->device_site_value) && $this->isSettingEmpty($setting['value'])) continue;
                if (isset($currentSetting->device_site_value) && $currentSetting->device_site_value === $setting['value']) continue;

                if (isset($currentSetting->device_site_value) && $currentSetting->device_site_value !== $setting['value']) {
                    DeviceSiteSetting::where([
                        ['dss_setting_id', $currentSetting->setting_id],
                        ['dss_ds_id', $deviceSite->ds_id],
                    ])->update(['dss_value' => $setting['value']]);
                    continue;
                }
                if (!isset($currentSetting->device_site_value) && !$this->isSettingEmpty($setting['value'])) {
                    DeviceSiteSetting::create([
                        'dss_ds_id' => $deviceSite->ds_id,
                        'dss_setting_id' => $currentSetting->setting_id,
                        'dss_value' => $setting['value'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
        return true;
    }

    public function updateLabelSettings(DeviceLabel $label, StdCollection $updateSettings): bool
    {
        $updateSettings = $this->trimStringsInCollectionRecursively($updateSettings);

        try {
            $currentLabelSettings = $this->getLabelSettings($label)->keyBy('setting_id');

            foreach ($updateSettings as $setting) {
                $currentSetting = $currentLabelSettings[$setting['setting_id']];

                if (!empty($setting['not_applicable']) && $this->isSettingEmpty($setting['value'])) {
                    DeviceLabelSetting::updateOrCreate(
                        ['dls_dl_id' => $label->dl_id, 'dls_setting_id' => $currentSetting->setting_id],
                        ['dls_value' => ''],
                    );
                    continue;
                }
                if (isset($currentSetting->label_value) && $this->isSettingEmpty($setting['value'])) {
                    DeviceLabelSetting::where([
                        ['dls_setting_id', $currentSetting->setting_id],
                        ['dls_dl_id', $label->dl_id],
                    ])->delete();
                    continue;
                }

                if (!isset($currentSetting->label_value) && $this->isSettingEmpty($setting['value'])) continue;
                if (isset($currentSetting->label_value) && $currentSetting->label_value === $setting['value']) continue;

                if (isset($currentSetting->label_value) && $currentSetting->label_value !== $setting['value']) {
                    DeviceLabelSetting::where([
                        ['dls_setting_id', $currentSetting->setting_id],
                        ['dls_dl_id', $label->dl_id],
                    ])->update(['dls_value' => $setting['value']]);
                    continue;
                }
                if (!isset($currentSetting->label_value) && !$this->isSettingEmpty($setting['value'])) {
                    DeviceLabelSetting::create([
                        'dls_dl_id' => $label->dl_id,
                        'dls_setting_id' => $currentSetting->setting_id,
                        'dls_value' => $setting['value'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
        return true;
    }

    public function updateAccountModuleSettings(Module $module, int $accountId, StdCollection $updateSettings, ?bool $useSettables = null): bool
    {
        $updateSettings = $this->trimStringsInCollectionRecursively($updateSettings);

        if (!is_null($useSettables)) {
            if ($useSettables) {
                $updateSettings = $this->applySettables($updateSettings, $module->modules_settables);
            } else {
                $updateSettings = $this->removeSettables($updateSettings, $module->modules_settables);
            }
        }

        try {
            $currentAccountModuleSettings = $this->getAccountModuleSettings($module->module_id, $accountId)->keyBy('setting_id');
            foreach ($updateSettings as $setting) {
                $currentSetting = $currentAccountModuleSettings[$setting['setting_id']];

                if (!empty($setting['not_applicable']) && $this->isSettingEmpty($setting['value'])) {
                    AccountModuleSetting::updateOrCreate(
                        ['ams_setting_id' => $currentSetting->setting_id, 'ams_module_id' => $module->module_id, 'ams_account_id' => $accountId],
                        ['ams_value' => ''],
                    );
                    continue;
                }
                if (isset($currentSetting->account_module_value) && $this->isSettingEmpty($setting['value'])) {
                    AccountModuleSetting::where([
                        ['ams_setting_id', $currentSetting->setting_id],
                        ['ams_module_id', $module->module_id],
                        ['ams_account_id', $accountId],
                    ])->delete();
                    continue;
                }

                if (!isset($currentSetting->account_module_value) && $this->isSettingEmpty($setting['value'])) continue;
                if (isset($currentSetting->account_module_value) && $currentSetting->account_module_value === $setting['value']) continue;

                if (isset($currentSetting->account_module_value) && $currentSetting->account_module_value !== $setting['value']) {
                    AccountModuleSetting::where([
                        ['ams_setting_id', $currentSetting->setting_id],
                        ['ams_module_id', $module->module_id],
                        ['ams_account_id', $accountId],
                    ])->update(['ams_value' => $setting['value']]);
                    continue;
                }
                if (!isset($currentSetting->account_module_value) && !$this->isSettingEmpty($setting['value'])) {
                    AccountModuleSetting::create([
                        'ams_account_id' => $accountId,
                        'ams_module_id' => $module->module_id,
                        'ams_setting_id' => $currentSetting->setting_id,
                        'ams_value' => $setting['value'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
        return true;
    }

    public function updateAccountSettings(int $accountId, StdCollection $updateSettings): bool
    {
        $updateSettings = $this->trimStringsInCollectionRecursively($updateSettings);

        try {
            $currentAccountSettings = $this->getAccountSettings($accountId)->keyBy('setting_id');
            foreach ($updateSettings as $setting) {
                $currentSetting = $currentAccountSettings[$setting['setting_id']];

                if (!empty($setting['not_applicable']) && $this->isSettingEmpty($setting['value'])) {
                    AccountSetting::updateOrCreate(
                        ['as_setting_id' => $currentSetting->setting_id, 'as_account_id' => $accountId],
                        ['as_value' => ''],
                    );
                    continue;
                }
                if (isset($currentSetting->account_value) && $this->isSettingEmpty($setting['value'])) {
                    AccountSetting::where([
                        ['as_setting_id', $currentSetting->setting_id],
                        ['as_account_id', $accountId],
                    ])->delete();
                    continue;
                }

                if (!isset($currentSetting->account_value) && $this->isSettingEmpty($setting['value'])) continue;
                if (isset($currentSetting->account_value) && $currentSetting->account_value === $setting['value']) continue;

                if (isset($currentSetting->account_value) && $currentSetting->account_value !== $setting['value']) {
                    AccountSetting::where([
                        ['as_setting_id', $currentSetting->setting_id],
                        ['as_account_id', $accountId],
                    ])->update(['as_value' => $setting['value']]);
                    continue;
                }
                if (!isset($currentSetting->account_value) && !$this->isSettingEmpty($setting['value'])) {
                    AccountSetting::create([
                        'as_account_id' => $accountId,
                        'as_setting_id' => $currentSetting->setting_id,
                        'as_value' => $setting['value'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
        return true;
    }

    public function applySettables(StdCollection $settings, Collection $settables, string $idKey = 'setting_id'): StdCollection
    {
        return $settings->filter(fn($setting) => $settables->contains('ms_setting_id', $setting[$idKey]));
    }

    public function removeSettables(StdCollection $settings, Collection $settables, string $idKey = 'setting_id'): StdCollection
    {
        return $settings->reject(fn($setting) => $settables->contains('ms_setting_id', $setting[$idKey]));
    }

    public function removePrefixedSettings(StdCollection $settings, string $prefix): StdCollection
    {
        return $settings->reject(fn($setting) => str_starts_with($setting->setting_key, $prefix));
    }

    public function sorts(StdCollection $settings, string $prefix): StdCollection
    {
        return $settings->reject(fn($setting) => str_starts_with($setting->setting_key, $prefix));
    }

    public function replaceBoolSettingsWithBoolValues(array $settings): array
    {
        $settingTypes = $this->getSettings()->pluck('setting_type.st_type', 'setting_id')->toArray();

        foreach ($settings as $settingId => $value) {
            if ($settingTypes[$settingId] === 'bool') {
                $settings[$settingId] = !empty($value);
            }
        }

        return $settings;
    }

    // not needed for 3-state toggle
    public function applyStringToBoolConversion(Collection $settings): Collection
    {
        foreach ($settings as $setting) {
            if ($setting->setting_type->st_type != 'bool') continue;
            foreach ($this->getSettingsOrder() as $key) {
                if (!isset($setting->{$key.'_value'})) continue;
                if ($setting->{$key.'_value'} === '1') {
                    $setting->{$key.'_value'} = true;
                }
                if ($setting->{$key.'_value'} === '0') {
                    $setting->{$key.'_value'} = false;
                }
            }
        }
        return $settings;
    }

    public function convertBoolToString(mixed $value): mixed
    {
        $value = ($value === false) ? '0' : $value;
        return   ($value === true)  ? '1' : $value;
    }

    public function isSettingEmpty(mixed $value): bool
    {
        $value = trim($value);
        return empty($value) && ($value !== '0');
    }
    public function getAllSettings(int $accountId = null, int $hostId = null, array $settings = [])
    {
        $result = [];
        $settings = Setting::all();
        $settingsMap = $settings->pluck('setting_key', 'setting_id')->toArray();

        $settings = $settings->pluck('setting_value', 'setting_id')->toArray();
        foreach ($settings as $id => $value) {
            $result[$settingsMap[$id]]['settings'] = $value;
        }

        // todo: implement host settings
        // $hostSettings = HostSetting::all()->toArray();

        $accountSettings = AccountSetting::all()->toArray();
        foreach ($accountSettings as $accSetting) {
            if (!empty($accountId) && $accountId !== $accSetting['as_account_id']) continue;
            $result[$settingsMap[$accSetting['as_setting_id']]]['acc_settings'][$accSetting['as_account_id']] = $accSetting['as_value'];
        }

        $moduleSettings = ModuleSetting::all()->toArray();
        foreach ($moduleSettings as $modSetting) {
            $result[$settingsMap[$modSetting['ms_setting_id']]]['mod_settings'][$modSetting['ms_module_id']] = $modSetting['ms_value'];
        }

        $accountModuleSettings = AccountModuleSetting::all()->toArray();
        foreach ($accountModuleSettings as $accModSetting) {
            if (!empty($accountId) && $accountId !== $accModSetting['ams_account_id']) continue;
            $result[$settingsMap[$accModSetting['ams_setting_id']]]['acc_mod_settings'][$accModSetting['ams_account_id']][$accModSetting['ams_module_id']] = $accModSetting['ams_value'];
        }

        $deviceSiteSettings = DeviceSiteSetting::all()->toArray();
        foreach ($deviceSiteSettings as $dsSetting) {
            $result[$settingsMap[$dsSetting['dss_setting_id']]]['ds_settings'][$dsSetting['dss_ds_id']] = $dsSetting['dss_value'];
        }

        // label settings
        $labelSettings = $this->getLabelsSettingsMap($settingsMap);
        foreach ($labelSettings as $settingKey => $settings) {
            $result[$settingKey] = array_merge($result[$settingKey] ?? [], $settings);
        }

        $deviceSettings = DeviceSetting::all()->toArray();
        foreach ($deviceSettings as $deviceSetting) {
            $result[$settingsMap[$deviceSetting['ds_setting_id']]]['device_settings'][$deviceSetting['ds_device_id']] = $deviceSetting['ds_value'];
        }

        return $result;
    }

    private function getLabelsSettingsMap(array $settingsMap): array
    {
        $labelSettings = DeviceLabelSetting::with(['device_label' => function($query) {
                $query->with('group')->orderBy('dl_order', 'asc');
        }])->get()->groupBy('dls_setting_id')->map(function($settingsGroup) {
            return $settingsGroup->map(function($setting) {
                $label = $setting->device_label;
                return [
                    'value' => $setting->dls_value,
                    'labelId' => $label->dl_id,
                    'priority' => [
                        'groupOrder' => $label->group ? $label->group->dlg_order : PHP_INT_MAX,
                        'labelOrder' => $label->dl_order
                    ],
                    'label_info' => $label->group
                        ? "{$label->group->dlg_name} -> {$label->dl_name}"
                        : $label->dl_name
                ];
            });
        })->toArray();

        $labelSites = DeviceLabelSite::all()->groupBy('dld_ds_id')->map(function($items) {
                return $items->pluck('dld_dl_id')->toArray();
        })->toArray();

        $result = [];
        foreach ($labelSettings as $settingId => $settings) {
            foreach ($labelSites as $siteId => $siteLabelIds) {
                $applicableSettings = array_filter($settings, fn($s) => in_array($s['labelId'], $siteLabelIds));
                if (empty($applicableSettings)) continue;

                usort($applicableSettings, fn($a, $b) =>
                    $a['priority']['groupOrder'] <=> $b['priority']['groupOrder'] ?:
                    $a['priority']['labelOrder'] <=> $b['priority']['labelOrder']
                );

                $finalSetting = array_shift($applicableSettings);
                $result[$settingsMap[$settingId]]['label_settings'][$siteId] = $finalSetting['value'];
                $result[$settingsMap[$settingId]]['label_sources'][$siteId] = $finalSetting['label_info'];
            }
        }

        return $result;
    }


    public function getPlainDeviceSettings(Device $device)
    {
        $returnSettings = [];
        $deviceSettings = $this->getDeviceSettings($device);
        foreach ($deviceSettings as $setting) {
            $returnSettings[$setting->setting_key]['setting_id'] = $setting->setting_id;
            $returnSettings[$setting->setting_key]['value'] = '';
            foreach ($this->getSettingsOrder() as $fallback) {
                if (!empty($setting->{$fallback.'_value'})) {
                    $returnSettings[$setting->setting_key]['value'] = $setting->{$fallback.'_value'};
                    break;
                }
            }
        }
        return $returnSettings;
    }

    public function getPlainSiteSettings(DeviceSite $site)
    {
        $returnSettings = [];
        $siteSettings = $this->getDeviceSiteSettings($site);
        foreach ($siteSettings as $setting) {
            $returnSettings[$setting->setting_key]['setting_id'] = $setting->setting_id;
            $returnSettings[$setting->setting_key]['value'] = '';
            foreach ($this->getSettingsOrder('device') as $fallback) {
                if (!empty($setting->{$fallback.'_value'})) {
                    $returnSettings[$setting->setting_key]['value'] = $setting->{$fallback.'_value'};
                    break;
                }
            }
        }
        return $returnSettings;
    }
}