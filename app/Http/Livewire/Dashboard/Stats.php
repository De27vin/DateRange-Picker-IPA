<?php
namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\Device;
use App\Services\DeviceAlertsService;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use App\Traits\AccountsTrait;
use App\Traits\DevicesTrait;
use Livewire\Component;

class Stats extends Component
{
    use WithPerPagePagination;
    use SearchFiltersTrait;
    use TranslationsTrait;
    use AccountsTrait;
    use DevicesTrait;

//    private $alerts;
    public $alertsCountGrouped;
    private $stats;

    private DeviceAlertsService $alertsService;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->alertsService = new DeviceAlertsService();
    }

    public function mount()
    {
        $this->initAlertData();
        $this->updateDashboardStats();
    }

    public function render()
    {
        return view('livewire.dashboard.stats', ['stats' => $this->stats]);
    }

    public function initAlertData()
    {
        $this->alerts = $this->alertsService->getAlertsGrouping();
        $this->alertsCount = $this->alertsService->getAllAlertCounts(session('account.id'));
        $this->alertsCountGrouped = $this->alertsService->getGroupedAlertsCounts(session('account.id'));
    }


    public function updateDashboardStats()
    {
        $enabledCount = Device::enabled()->get()?->count() ?? 0;
        $devicesCount = [
            'enabled' => $enabledCount,
            'disabled' => Device::disabled()->get()?->count() ?? 0,
        ];

        $localChecks = $this->alertsCountGrouped['all']['TECH'] ?? 0;
        $periodicalCalls = $this->alertsCountGrouped['all']['PERIODICAL'] ?? 0;

        $majorSum = array_sum($this->alertsCountGrouped['critical']);
        $minorSum = array_sum($this->alertsCountGrouped['normal']);

        if ($enabledCount) {
            $serviceLevelCalls = (($enabledCount - $periodicalCalls - $majorSum) / $enabledCount) * 100;
            $serviceLevelChecks = (($enabledCount - $localChecks - $minorSum) / $enabledCount) * 100;
        } else {
            $serviceLevelCalls = 0;
            $serviceLevelChecks = 0;
        }
        $serviceLevelCalls = (($serviceLevelCalls > 0) ? round($serviceLevelCalls, 0) : '0') . '%';
        $serviceLevelChecks = (($serviceLevelChecks > 0) ? round($serviceLevelChecks, 0) : '0') . '%';


        $this->stats = [
            [
                'values' => $devicesCount,
                'label' => __('Equipment'),
                'color' => '#d1d1d1',
                'text-color' => '#767676'
            ],
            [
                'values' => [__('inbound calls') => $this->alertsCountGrouped['all']['VOICE'] ?? 0, __('active alarms') => array_sum($this->alertsCountGrouped['alarming']) ?? 0],
                'label' => __('Alarms'),
                'color' => '#ddb2b1',
                'text-color' => '#953735'
            ],
            [
                'values' => [__('periodic calls') => $periodicalCalls, __('local checks') => $localChecks],
                'label' => __('Overdues'),
                'color' => '#b2c5dc',
                'text-color' => '#355c8c'
            ],
            [
                'values' => [__('major') => $majorSum, __('minor') => $minorSum],
                'label' => __('Alerts'),
                'color' => '#edc8aa',
                'text-color' => '#db6709'
            ],
            [
                'values' => [__('automated checks') => $serviceLevelCalls, __('physical checks') => $serviceLevelChecks],
                'label' => __('Service Level'),
                'color' => '#d3e0ba',
                'text-color' => '#6e8837'
            ],
        ];
    }

//    private function countServiceLevel(int $enabledCount)
//    {
//        $countDevicesWithCriticalAlerts = $this->getAlertDevices('all', $this->alerts['critical'])->count();
//
//        if ($enabledCount > 0) {
//            $serviceLevel = ($enabledCount - $countDevicesWithCriticalAlerts) / $enabledCount * 100;
//        } else {
//            $serviceLevel = 0;
//        }
//
//        return round($serviceLevel,0) . '%';
//    }

}
