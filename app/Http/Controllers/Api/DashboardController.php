<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use App\Services\CustomFieldsService;
use App\Services\SearchDeviceService;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    use SearchFiltersTrait;
    use TranslationsTrait;

    private SearchDeviceService $searchService;
    private CustomFieldsService $customFieldsService;

    public function __construct() {
        $this->searchService = new SearchDeviceService();
        $this->customFieldsService = new CustomFieldsService();
    }

    public function getDevices()
    {
        $this->checks();

        if (request()->has('filters')) {
            $filters = request()->input('filters');
            $this->updateDeviceSearchFilter($filters, 'Dashboard');
        } else {
            $filters = $this->getDeviceSearchFilter('Dashboard');
        }

        $devicesQuery = Device::query();
        $devicesQuery->where('device_enabled', true);
        $query = $this->searchService->buildDevicesQuery($filters, true, $devicesQuery, excludeGateways: true);
        $devices = $query->paginate(50);


        return $devices;
    }

    public function getBasfDevices()
    {
        $this->checks();

        $alarmality = $this->getAlertAlarmalityStates();
        $alarmality = array_filter($alarmality);

        if (request()->has('filters')) {
            $filters = request()->input('filters');
            $this->updateDeviceSearchFilter($filters, 'Dashboard');
        } else {
            $filters = $this->getDeviceSearchFilter('Dashboard');
        }

        $filters['alerts'] = $alarmality;
        $baseQuery = Device::query()->where('device_enabled', true);
        $query = $this->searchService->buildDevicesQuery($filters, false, $baseQuery, excludeGateways: true);
        $devices = $query->paginate(200);

        return $devices;
    }

    private function checks()
    {
        if (empty(session('account.id'))) {
            \Log::error('empty account id in '.__FILE__.' '.__METHOD__);
            abort(500);
        }
    }
}
