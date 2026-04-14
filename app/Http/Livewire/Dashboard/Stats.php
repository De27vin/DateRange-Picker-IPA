<?php
namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\Device;
use App\Services\DatabaseTimeseriesLoader;
use App\Services\DeviceAlertsService;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use App\Traits\AccountsTrait;
use App\Traits\DevicesTrait;
use Carbon\CarbonImmutable;
use Livewire\Component;

class Stats extends Component
{
    use WithPerPagePagination;
    use SearchFiltersTrait;
    use TranslationsTrait;
    use AccountsTrait;
    use DevicesTrait;

    private const ALERT_SERIES_TO_TYPE = [
        'active_alarm' => 'ALARM',
        'battery_malfunction' => 'BATDEF',
        'battery_low' => 'BATLOW',
        'button_malfunction' => 'BUTTON',
        'charge_malfunction' => 'CHARGE',
        'database_malfunction' => 'DB',
        'disk_low' => 'DISK',
        'object_door_failure' => 'LOCATION',
        'elevator_failure' => 'ELEVATOR',
        'gateway_malfunction' => 'GATEWAY',
        'identity_mismatch' => 'IDENTITY',
        'line_alarm' => 'LINE',
        'object_is_under_maintenance' => 'MAINTENANCE',
        'microphone_malfunction' => 'MIC',
        'network_malfunction' => 'NETWORK',
        'periodical_call_overdue' => 'PERIODICAL',
        'pin_mismatch' => 'PIN',
        'power_malfunction' => 'POWER',
        'ram_low' => 'RAM',
        'reserved_device' => 'RESERVE',
        'serial_port_malfunction' => 'SERIAL',
        'shaft_failure' => 'SHAFT',
        'low_signal' => 'SIGNAL',
        'sip_registration_failure' => 'SIP',
        'speaker_malfunction' => 'SPEAKER',
        'technician_check_overdue' => 'TECH',
        'voice_alarm' => 'VOICE',
    ];

    public $alertsCountGrouped;
    private $stats;

    private DeviceAlertsService $alertsService;
    private DatabaseTimeseriesLoader $timeseriesLoader;

    public function boot(): void
    {
        $this->alertsService = app(DeviceAlertsService::class);
        $this->timeseriesLoader = app(DatabaseTimeseriesLoader::class);
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
        $this->initAlertData();

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

        [$startUtc, $endUtc] = $this->previewWindowUtc();
        $equipmentPreviewRows = $this->loadHourlyChartSeries('EquipmentChart', ['enabled', 'disabled'], $startUtc, $endUtc);
        $alarmPreviewRows = $this->loadHourlyChartSeries('AlarmChart', ['inbound_calls', 'active_alarms'], $startUtc, $endUtc);
        $alertsPreviewSourceRows = $this->loadHourlyChartSeries('AlertsChart', array_keys(self::ALERT_SERIES_TO_TYPE), $startUtc, $endUtc);
        $overduesPreviewRows = $this->loadHourlyChartSeries('ServiceLevelChart', ['periodical_calls', 'local_checks'], $startUtc, $endUtc);
        $alertsPreviewRows = $this->buildAlertsPreviewRows($alertsPreviewSourceRows);
        $servicePreviewRows = $this->buildServiceLevelPreviewRows($equipmentPreviewRows, $overduesPreviewRows, $alertsPreviewRows);
        $dateLabels = $this->previewDateLabels($endUtc);

        $this->stats = [
            [
                'values' => $this->formatValues($devicesCount),
                'label' => __('Equipment'),
                'color' => '#d1d1d1',
                'text-color' => '#767676',
                'preview' => $this->buildPreview(
                    $equipmentPreviewRows,
                    [
                        ['key' => 'enabled', 'color' => '#767676'],
                        ['key' => 'disabled', 'color' => '#4b5563'],
                    ],
                    $dateLabels
                ),
            ],
            [
                'values' => $this->formatValues([
                    __('inbound calls') => $this->alertsCountGrouped['all']['VOICE'] ?? 0,
                    __('active alarms') => array_sum($this->alertsCountGrouped['alarming']) ?? 0,
                ]),
                'label' => __('Alarms'),
                'color' => '#ddb2b1',
                'text-color' => '#953735',
                'preview' => $this->buildPreview(
                    $alarmPreviewRows,
                    [
                        ['key' => 'inbound_calls', 'color' => '#c17579'],
                        ['key' => 'active_alarms', 'color' => '#953735'],
                    ],
                    $dateLabels
                ),
            ],
            [
                'values' => $this->formatValues([
                    __('periodic calls') => $periodicalCalls,
                    __('local checks') => $localChecks,
                ]),
                'label' => __('Overdues'),
                'color' => '#b2c5dc',
                'text-color' => '#355c8c',
                'preview' => $this->buildPreview(
                    $overduesPreviewRows,
                    [
                        ['key' => 'periodical_calls', 'color' => '#4b78a8'],
                        ['key' => 'local_checks', 'color' => '#355c8c'],
                    ],
                    $dateLabels
                ),
            ],
            [
                'values' => $this->formatValues([
                    __('major') => $majorSum,
                    __('minor') => $minorSum,
                ]),
                'label' => __('Alerts'),
                'color' => '#edc8aa',
                'text-color' => '#db6709',
                'preview' => $this->buildPreview(
                    $alertsPreviewRows,
                    [
                        ['key' => 'major', 'color' => '#db6709'],
                        ['key' => 'minor', 'color' => '#b45309'],
                    ],
                    $dateLabels
                ),
            ],
            [
                'values' => $this->formatValues([
                    __('automated checks') => $serviceLevelCalls,
                    __('physical checks') => $serviceLevelChecks,
                ]),
                'label' => __('Service Level'),
                'color' => '#d3e0ba',
                'text-color' => '#6e8837',
                'preview' => $this->buildPreview(
                    $servicePreviewRows,
                    [
                        ['key' => 'automated_checks', 'color' => '#6e8837'],
                        ['key' => 'physical_checks', 'color' => '#4d7c0f'],
                    ],
                    $dateLabels
                ),
            ],
        ];
    }

    private function previewWindowUtc(): array
    {
        $endUtc = CarbonImmutable::now('UTC')->startOfHour();
        $startUtc = $endUtc->subHours(72);

        return [$startUtc, $endUtc];
    }

    private function loadHourlyChartSeries(string $chart, array $keys, CarbonImmutable $startUtc, CarbonImmutable $endUtc): array
    {
        $rawRows = $this->timeseriesLoader->load($chart, $startUtc, $endUtc);
        $rowsByTs = [];

        foreach ($rawRows as $row) {
            $rowsByTs[$row['ts']] = is_array($row['series'] ?? null) ? $row['series'] : [];
        }

        $filledRows = [];
        $lastSeries = array_fill_keys($keys, 0);
        $cursor = $startUtc;

        while ($cursor->lte($endUtc)) {
            $ts = $cursor->toIso8601String();
            $series = $rowsByTs[$ts] ?? [];

            foreach ($keys as $key) {
                if (array_key_exists($key, $series)) {
                    $lastSeries[$key] = (int) $series[$key];
                }
            }

            $filledRows[] = [
                'ts' => $ts,
                'series' => $lastSeries,
            ];

            $cursor = $cursor->addHour();
        }

        return $filledRows;
    }

    private function buildAlertsPreviewRows(array $rows): array
    {
        $criticalTypes = array_flip($this->alertsCountGrouped['critical'] ?? []);
        $normalTypes = array_flip($this->alertsCountGrouped['normal'] ?? []);

        return array_map(function (array $row) use ($criticalTypes, $normalTypes): array {
            $major = 0;
            $minor = 0;

            foreach (($row['series'] ?? []) as $seriesKey => $value) {
                $alertType = self::ALERT_SERIES_TO_TYPE[$seriesKey] ?? null;
                if ($alertType === null) {
                    continue;
                }

                if (isset($criticalTypes[$alertType])) {
                    $major += (int) $value;
                }

                if (isset($normalTypes[$alertType])) {
                    $minor += (int) $value;
                }
            }

            return [
                'ts' => $row['ts'],
                'series' => [
                    'major' => $major,
                    'minor' => $minor,
                ],
            ];
        }, $rows);
    }

    private function buildServiceLevelPreviewRows(array $equipmentRows, array $overduesRows, array $alertsRows): array
    {
        $count = min(count($equipmentRows), count($overduesRows), count($alertsRows));
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $enabled = (int) ($equipmentRows[$index]['series']['enabled'] ?? 0);
            $periodicalCalls = (int) ($overduesRows[$index]['series']['periodical_calls'] ?? 0);
            $localChecks = (int) ($overduesRows[$index]['series']['local_checks'] ?? 0);
            $major = (int) ($alertsRows[$index]['series']['major'] ?? 0);
            $minor = (int) ($alertsRows[$index]['series']['minor'] ?? 0);

            $automatedChecks = $enabled > 0
                ? max(0, (int) round((($enabled - $periodicalCalls - $major) / $enabled) * 100))
                : 0;
            $physicalChecks = $enabled > 0
                ? max(0, (int) round((($enabled - $localChecks - $minor) / $enabled) * 100))
                : 0;

            $rows[] = [
                'ts' => $equipmentRows[$index]['ts'] ?? $overduesRows[$index]['ts'] ?? $alertsRows[$index]['ts'],
                'series' => [
                    'automated_checks' => $automatedChecks,
                    'physical_checks' => $physicalChecks,
                ],
            ];
        }

        return $rows;
    }

    private function previewDateLabels(CarbonImmutable $endUtc): array
    {
        $timezone = auth()->user()?->user_timezone ?: 'Europe/Zurich';
        $endLocal = $endUtc->setTimezone($timezone);

        return [
            $endLocal->subDays(2)->format('j.n.'),
            $endLocal->subDay()->format('j.n.'),
            $endLocal->format('j.n.'),
        ];
    }

    private function buildPreview(array $rows, array $seriesDefinitions, array $dateLabels): array
    {
        $width = 144.0;
        $height = 60.0;
        $maxValue = 1;

        foreach ($rows as $row) {
            foreach ($seriesDefinitions as $seriesDefinition) {
                $maxValue = max($maxValue, (int) (($row['series'][$seriesDefinition['key']] ?? 0)));
            }
        }

        $series = [];
        foreach ($seriesDefinitions as $seriesDefinition) {
            $values = array_map(
                fn (array $row): int => (int) (($row['series'][$seriesDefinition['key']] ?? 0)),
                $rows
            );

            $series[] = [
                'key' => $seriesDefinition['key'],
                'color' => $seriesDefinition['color'],
                'points' => $this->sparklinePoints($values, $width, $height, $maxValue),
                'lastPoint' => $this->sparklineLastPoint($values, $width, $height, $maxValue),
            ];
        }

        return [
            'width' => $width,
            'height' => $height,
            'series' => $series,
            'labels' => $dateLabels,
        ];
    }

    private function sparklinePoints(array $values, float $width, float $height, int $maxValue): string
    {
        if ($values === []) {
            return '';
        }

        $lastIndex = max(count($values) - 1, 1);
        $points = [];

        foreach ($values as $index => $value) {
            $x = count($values) === 1 ? ($width / 2) : ($width * ($index / $lastIndex));
            $y = $height - (($value / $maxValue) * $height);
            $points[] = round($x, 2) . ',' . round($y, 2);
        }

        return implode(' ', $points);
    }

    private function sparklineLastPoint(array $values, float $width, float $height, int $maxValue): array
    {
        if ($values === []) {
            return ['x' => 0, 'y' => $height];
        }

        $x = count($values) === 1 ? ($width / 2) : $width;
        $y = $height - (($values[count($values) - 1] / $maxValue) * $height);

        return [
            'x' => round($x, 2),
            'y' => round($y, 2),
        ];
    }

    private function formatValues(array $values): array
    {
        $formatted = [];

        foreach ($values as $label => $value) {
            $formatted[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $formatted;
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
