<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GroupCache;
use App\Helpers\Ucp;
use App\Http\Requests\Equipment\SaveSiteRequest;
use App\Models\Address;
use App\Models\Device;
use App\Models\DeviceComment;
use App\Models\DeviceGateway;
use App\Models\DeviceLabelGroup;
use App\Models\DeviceSite;
use App\Models\Location;
use App\Models\Session;
use App\Services\AddressService;
use App\Services\CustomFieldsService;
use App\Services\DeviceFormFieldsService;
use App\Services\NotificationsService;
use App\Services\PhoneNumbersService;
use App\Services\SearchDeviceService;
use App\Services\SettingsService;
use App\Services\SitePersistenceService;
use App\Services\SiteValidationService;
use App\Traits\DeviceFormTrait;
use App\Traits\FreeswitchApiTrait;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use App\Traits\ValidationTraitNew;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\DTO\{SiteDTO};

class EquipmentController extends BaseController
{
    use FreeswitchApiTrait;
    use SearchFiltersTrait;
    use DeviceFormTrait;
    use TranslationsTrait;
//    use ValidationTraitNew;

    public function __construct(
        private readonly SitePersistenceService  $sitePersistenceService,
        private readonly SearchDeviceService     $searchService,
        private readonly CustomFieldsService     $customFieldsService,
        private readonly SettingsService         $settingsService,
        private readonly PhoneNumbersService     $numbersService,
        private readonly AddressService          $addressService,
        private readonly DeviceFormFieldsService $formFieldsService,
        private readonly NotificationsService    $notifications,
        private readonly SiteValidationService   $siteValidation,
    ) {}


    public function saveSite(SaveSiteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $siteModel = DeviceSite::findOrFail($validated['ds_id']);
        $siteDTO = SiteDTO::fromArray($validated);

        if ($dtoErrors = $this->siteValidation->validateSiteDTO($siteDTO, $siteModel)) {
            return $this->errorResponse([], 422);
        }

        $options = [
            'updateCli' => $validated['updateCli']
        ];

        try {
            DB::beginTransaction();
            $this->sitePersistenceService->persistSite($siteModel, $siteDTO, $options);
            DB::commit();

            GroupCache::forgetGroup('sites');
            GroupCache::forgetGroup('devices');
            GroupCache::forgetGroup('settings');
            GroupCache::forgetGroup('gateways');
            GroupCache::forgetGroup('numbers');

            return $this->successResponse($siteDTO->dsId);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Site update failed', ['e' => $e]);
            
            if (str_contains($e->getMessage(), 'QR Code value must be unique')) {
                $this->notifications->add('error', __('QR Code value must be unique within the account. This value already exists on another device.'));
            } else {
                $this->notifications->add('error', __('Site update failed'));
            }
            
            return $this->errorResponse([], 500);
        }
    }

    private function successResponse(int $siteId, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'site' => $this->getSavedSite($siteId),
            'errors' => [],
            'notifications' => $this->notifications->get(),
            'settings' => $this->getSettings()
        ], $status);
    }

    private function errorResponse(array $errors, int $status = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
            'notifications' => $this->notifications->get()
        ], $status);
    }

    public function filterTest()
    {
        return $this->getDeviceSearchFilter('Equipment');
    }

    public function getSites()
    {
        $this->checks();

        if (request()->has('filters')) {
            $filters = request()->input('filters');
            $this->updateDeviceSearchFilter($filters, 'Equipment');
        } else {
            $filters = $this->getDeviceSearchFilter('Equipment');
        }

        $cacheKey = __CLASS__ . __METHOD__ .'_'. session('account.id') .'_'. md5(json_encode($filters)).'_'.(request('page') ?? 1);
        return GroupCache::remember('sites', $cacheKey, 120, function() use ($filters) {
            $query = $this->searchService->buildDeviceSitesQuery($filters);
            $deviceSites = $query->paginate(50);
            return $deviceSites->toArray();
        });
    }

    public function getSite()
    {
        $this->checks();
        $siteId = request()->post('siteId');

        $cacheKey = __CLASS__ . __METHOD__ . $siteId;
        return GroupCache::remember('sites', $cacheKey, 60, function() use ($siteId) {

            return DeviceSite::with([
                'devices',
                'devices.gateway',
                'devices.custom_fields',
                'devices.module.funktions',
                'devices.module.module_type',
                'module',
                'module.module_type',
                'numbers',
                'address',
                'address.location',
                'custom_fields',
                'labels',
            ])->findOrFail($siteId);
        });
    }

    private function getSavedSite(int $siteId)
    {
        return DeviceSite::with([
            'devices',
            'devices.gateway',
            'devices.custom_fields',
            'devices.module.funktions',
            'devices.module.module_type',
            'module',
            'module.module_type',
            'numbers',
            'address',
            'address.location',
            'custom_fields',
            'labels',
        ])->find($siteId);
    }

    public function confirmSetField()
    {
        $notifications = ['error' => [], 'success' => [], 'warning' => [], 'info' => []];
        $deviceId = request('deviceId');
        $field = request('field');
        in_array($field, ['pin', 'identity', 'module']) || abort(400);
        $device = Device::findOrFail($deviceId);

        $success = false;
        DB::beginTransaction();
        try {

            $affected = DB::table('devices')
            ->where('device_id', $device->device_id)
            ->update([
                'device_set'.$field => null,
                'device_'.$field => $device->{'device_set'.$field}
            ]);

            DB::commit();
            $success = true;
            $notifications['success'][] = __('Device updated');
        } catch (\Throwable $e) {
            DB::rollback();
            \Log::error($e, ['Caught']);
            $notifications['error'][] = __('Error on device update');
        }

        if ($this->fsMake('ucp del device ' . $deviceId, false, true)) {
            $notifications['success'][] = __('ucp reload device command processed');
        } else {
            $notifications['warning'][] = __('ucp reload device command failed');
        }

        $device = $device->fresh()->load([
            'gateway',
            'custom_fields',
            'module.funktions',
        ]);

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');

        return [
            'device' => $device,
            'errors' => [],
            'notifications' => $notifications,
            'success' => $success
        ];
    }

    public function rejectSetField()
    {
        $notifications = ['error' => [], 'success' => [], 'warning' => [], 'info' => []];
        $deviceId = request('deviceId');
        $field = request('field');
        in_array($field, ['pin', 'identity', 'module']) || abort(400);
        $device = Device::findOrFail($deviceId);

        $success = false;
        DB::beginTransaction();
        try {

            $affected = DB::table('devices')
            ->where('device_id', $device->device_id)
            ->update([
                'device_set'.$field => null,
            ]);

            DB::commit();
            $success = true;
            $notifications['success'][] = __('Device updated');
        } catch (\Throwable $e) {
            DB::rollback();
            \Log::error($e, ['Caught']);
            $notifications['error'][] = __('Error on device update');
        }

        if ($this->fsMake('ucp del device ' . $deviceId, false, true)) {
            $notifications['success'][] = __('ucp reload device command processed');
        } else {
            $notifications['warning'][] = __('ucp reload device command failed');
        }

        $device = $device->fresh()->load([
            'gateway',
            'custom_fields',
            'module.funktions',
        ]);

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');

        return [
            'device' => $device,
            'errors' => [],
            'notifications' => $notifications,
            'success' => $success
        ];
    }

    public function deleteDevice()
    {
        $success = false;
        DB::beginTransaction();
        try {
            $deviceId = request()->post('deviceId');
            $device = Device::findOrFail($deviceId);

            if ($device->device_account_id != session('account.id')) {
                throw new \Exception('Attempt to delete device not belonging to user account');
            }

            if ($gateway = DeviceGateway::where('dg_device_id', $deviceId)->first()) {
                $gateway->dg_device_id = null;
                $gateway->save();
            }

            $device->device_settings()->delete();
            $device->delete();

            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Exception caught', ['e' => $e]);
        }

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');

        return ['success' => $success];
    }

    public function deleteSite()
    {
        $success = false;
        DB::beginTransaction();
        try {
            $siteId = request()->post('siteId');
            $site = DeviceSite::findOrFail($siteId);

            if ($site->ds_account_id != session('account.id')) {
                throw new \Exception('Attempt to delete site not belonging to user account');
            }

            if (count($site->devices)) {
                throw new \Exception('Attempt to delete site with connected devices');
            }

            // this possibility is deprecated
            if (!empty($site->device_gateway)) {
                throw new \Exception('Attempt to delete site with connected gateway');
            }

            $this->numbersService->detachNumbersFromSite($site);
            $site->device_site_settings()->delete();
            $site->delete();

            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            DB::rollback();
            \Log::error('Exception caught', ['e' => $e]);
        }

        if ($success) {
            $this->makeFsDeleteSite($site->ds_id);
        }

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');

        return ['success' => $success];
    }

    public function getSettings()
    {
        $this->checks();
        return $this->settingsService->getAllSettings(session('account.id'));
    }

    public function getSiteHistory()
    {
        $this->checks();
        $siteId = request()->post('siteId');

        $start = Ucp::stringDateToUTC(request()->post('start'), 'start');
        $end = Ucp::stringDateToUTC(request()->post('end'))->endOfDay();

        $site = DeviceSite::findOrFail($siteId);
        $devicesId = $site->devices->pluck('device_id')->toArray();

        $query = Session::with('session_type');
        $query->where(function ($query) use ($site, $devicesId) {
            $query->where('session_ds_id', $site->ds_id)->orWhereIn('session_device_id', $devicesId);
        });
        $query->whereBetween('session_start', [$start, $end]);
        $query->orderByDesc('session_id');

        return $query->get();
    }

    public function toggleDeviceState()
    {
        $success = false;
        DB::beginTransaction();
        try {
            $deviceId = request()->post('deviceId');
            $device = Device::findOrFail($deviceId);

            $device->device_enabled = !$device->device_enabled;
            $device->update(['device_enabled' => $device->device_enabled]);

            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Exception caught on device state toggle', ['e' => $e]);
        }

        if (!empty($device)) {
            $this->makeFsDeleteDevice($device->device_id);
            $device->refresh();
        }

        GroupCache::forgetGroup('sites');
        GroupCache::forgetGroup('devices');
        return ['success' => $success, 'device' => $device ?? null];
    }

    public function addComment()
    {
        $success = false;
        $deviceId = request()->post('deviceId');
        $newComment = request()->post('newComment');
        $link = request()->post('link');

        if (empty($deviceId) || empty($newComment)) {
            return [
                'success' => false,
                'errors' => [
                    'deviceId' => empty($deviceId) ? __('Field is required') : null,
                    'newComment' => empty($newComment) ? __('Field is required') : null,
                ]
            ];
        }

        $newComment = strip_tags($newComment);
        $link = filter_var($link, FILTER_SANITIZE_URL);

        DB::beginTransaction();
        try {
            $device = Device::findOrFail($deviceId);

            DeviceComment::create([
                'dc_device_id' => $device->device_id,
                'dc_user_id' => Auth::user()->user_id,
                'dc_text' => $newComment,
                'dc_link' => $link ?: null,
            ]);

            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Exception caught on adding comment to device', ['e' => $e]);
        }

        return ['success' => $success, 'comments' => $device->device_site->comments ?? null];
    }

    public function makeFsCall()
    {
        $action = request()->post('action');
        $deviceId = request()->post('deviceId');

        switch ($action) {
            case 'carcall':
                return $this->makeFsCarcall($deviceId);
            case 'trigger':
                return $this->makeFsTrigger($deviceId);
            case 'revival':
                return $this->makeFsRevival($deviceId);
            case 'set':
                return $this->makeFsSet($deviceId);
            default:
                // code...
                break;
        }

        return 'failure';
    }

    // todo: move freeswitch calls to service
    private function makeFsCarcall($deviceId)
    {
        if($result = $this->fsMake('ucp carcall device ' . $deviceId . ' ' . Auth::user()->user_ext)) {
            return 'success';
        } else {
            \Log::error('Failed Freeswith CARCALL');
            return 'failure';
        }
    }

    private function makeFsTrigger($deviceId)
    {
        if($result = $this->fsMake('ucp trigger device ' . $deviceId)) {
            return 'success';
        } else {
            return 'failure';
        }
    }

    private function makeFsSet($deviceId)
    {
        if($result = $this->fsMake('ucp set device ' . $deviceId)) {
            return 'success';
        } else {
            \Log::error('Failed Freeswith SET');
            return 'failure';
        }
    }

    private function makeFsRevival($deviceId)
    {
        if($result = $this->fsMake('ucp revive device ' . $deviceId)) {
            return 'success';
        } else {
            \Log::error('Failed Freeswith REVIVAL');
            return 'failure';
        }
    }

    private function makeFsDeleteSite($id)
    {
        return (bool) $this->fsMake('ucp del site ' . $id, false, true);
    }

    private function makeFsDeleteDevice($id)
    {
        return (bool) $this->fsMake('ucp del device ' . $id, false, true);
    }

    private function makeFsReloadDevice($deviceId)
    {
        return (bool) $this->fsMake('ucp del device ' . $deviceId, false, true);
    }

    private function checks()
    {
        if (empty(session('account.id'))) {
            \Log::error('empty account id in '.__FILE__.' '.__METHOD__);
            abort(500);
        }
    }
}
