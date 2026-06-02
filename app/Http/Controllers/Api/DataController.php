<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GroupCache;
use App\Models\DeviceLabelGroup;
use App\Models\Number;
use App\Services\CustomFieldsService;
use App\Services\DeviceFormFieldsService;
use App\Services\SettingsService;
use App\Traits\DeviceFormTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use App\Models\DeviceGateway;

class DataController extends BaseController
{
    use TranslationsTrait;
    use DeviceFormTrait;

    public function __construct(
        private readonly CustomFieldsService     $customFieldsService,
        private readonly SettingsService         $settingsService,
        private readonly DeviceFormFieldsService $formFieldsService,
    ) {}

    public function getCustomFieldsConfig()
    {
        $this->checks();
        $accountId = session('account.id');
        $cacheKey = __CLASS__.__METHOD__.$accountId;

        // Cache for 1 000 minutes (60 000 seconds)
        return GroupCache::remember('cfg', $cacheKey, 60000, function() use ($accountId) {
            return $this->customFieldsService->getAccountCustomFieldsConfig($accountId);
        });
    }

    public function generateQrCode()
    {
        $this->checks();
        $value = request()->input('value');
        if (!$value) {
            return response()->json(['error' => 'Value required'], 400);
        }

        try {
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($value);
            return response($qrCode)->header('Content-Type', 'image/svg+xml');
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', ['error' => $e->getMessage(), 'value' => $value]);
            return response()->json(['error' => 'QR generation failed'], 500);
        }
    }

    public function getLabels()
    {
        $this->checks();
        $accountId = session('account.id');
        $cacheKey = __CLASS__.__METHOD__.$accountId;

        // 100 minutes (6 000 seconds)
        return GroupCache::remember('labels', $cacheKey, 6000, function() use ($accountId) {
            return DeviceLabelGroup::with(['labels' => function($query) {
                $query->orderBy('dl_order');
            }])
            ->where('dlg_account_id', $accountId)
            ->orderBy('dlg_order')
            ->get();
        });
    }

    public function getSettings()
    {
        $this->checks();
        $accountId = session('account.id');
        $cacheKey = __CLASS__.__METHOD__.$accountId;

        // Cache for 100 minutes (6 000 seconds)
        return GroupCache::remember('settings', $cacheKey, 6000, function() use ($accountId) {
            return $this->settingsService->getAllSettings($accountId);
        });
    }

    public function getRequiredFields()
    {
        $accountId = session('account.id');
        $cacheKey = __CLASS__.__METHOD__.$accountId;

        // Cache for 1 000 minutes (60 000 seconds)
        return GroupCache::remember('modules', $cacheKey, 60000, function() {
            return $this->formFieldsService->getRequiredFieldsForAllModules();
        });
    }

    public function getCountries()
    {
        return GroupCache::rememberForever('global', 'countries', function() {
            return $this->getCountryList();
        });
    }

    public function getTranslations()
    {
        $locale = session('locale', 'en');
        $cacheKey = __CLASS__.__METHOD__.$locale;

        // Cache for 100 000 minutes (6 000 000 seconds)
        return GroupCache::rememberForever('translations', $cacheKey, function() use ($locale) {
            $translations = [];
            $langPath = resource_path("lang/{$locale}");

            $jsonPath = resource_path("lang/{$locale}.json");
            if (File::exists($jsonPath)) {
                $translations = json_decode(File::get($jsonPath), true) ?? [];
            }

            if (is_dir($langPath)) {
                foreach (File::allFiles($langPath) as $file) {
                    $phpTranslations = File::getRequire($file->getRealPath());
                    // ensure JSON keys keep precedence (matches behaviour of __())
                    $translations = array_merge($this->flattenArray($phpTranslations), $translations);
                }
            }

            return $translations;
        });
    }

    public function getAssignableGateways()
    {
        $this->checks();
        $accountId = session('account.id');
        $cacheKey = __CLASS__.__METHOD__.$accountId;

        // Cache for 100 minutes (6 000 seconds)
        return GroupCache::remember('gateways', $cacheKey, 6000, function() {
            return DeviceGateway::doesntHave('device')->get()->map(function (DeviceGateway $gateway) {
                $mac = $gateway->dg_mac ? $gateway->dg_mac.' (mac) ' : '';
                $imei = $gateway->dg_imei ? $gateway->dg_imei.' (imei) ' : '';
                $name = $mac ?: $imei ?: 'Undefined';
                return ['name' => $name, 'id' => $gateway->dg_id];
            })->toArray();
        });
    }


    public function getAssignableSipNumbers()
    {
        $this->checks();
        $accountId = session('account.id');
        $cacheKey = __CLASS__.__METHOD__.$accountId;

        // Cache for 100 minutes (6 000 seconds)
        return GroupCache::remember('numbers', $cacheKey, 6000, function() {
            return Number::query()
                ->whereHas('number_type', function($query) {
                    $query->where('nt_type', 'SIP');
                })
                ->where('number_account_id', session('account.id'))
                ->whereNull('number_ds_id')
                ->get()
                ->map(function (Number $number) {
                    return [
                        'name' => $number->number_value,
                        'id' => $number->number_id
                    ];
                })
                ->toArray();
        });
    }

    private function flattenArray(array $array, $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    private function checks()
    {
        if (empty(session('account.id'))) {
            \Log::error('empty account id in ' . __FILE__ . ' ' . __METHOD__);
            abort(500);
        }
    }
}
