<?php

namespace App\Services;

class TimeseriesSnapshotChartMapper
{
    private const ALERT_TYPE_MAP = [
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

    private const ALERT_TYPES = [
        'active_alarm',
        'battery_malfunction',
        'battery_low',
        'button_malfunction',
        'charge_malfunction',
        'database_malfunction',
        'disk_low',
        'object_door_failure',
        'elevator_failure',
        'gateway_malfunction',
        'identity_mismatch',
        'line_alarm',
        'object_is_under_maintenance',
        'microphone_malfunction',
        'network_malfunction',
        'periodical_call_overdue',
        'pin_mismatch',
        'power_malfunction',
        'ram_low',
        'reserved_device',
        'serial_port_malfunction',
        'shaft_failure',
        'low_signal',
        'sip_registration_failure',
        'speaker_malfunction',
        'technician_check_overdue',
        'voice_alarm',
    ];

    /**
     * @return array<string, int>
     */
    public function extractSeries(string $chart, array $snapshotData): array
    {
        return match ($chart) {
            'EquipmentChart' => [
                'enabled' => $this->intValue($this->path($snapshotData, ['devices', 'enabled'])),
                'disabled' => $this->intValue($this->path($snapshotData, ['devices', 'disabled'])),
            ],
            'AlarmChart' => [
                'inbound_calls' => $this->intValue($this->path($snapshotData, ['alarms', 'inbound_calls'])),
                'active_alarms' => $this->intValue($this->path($snapshotData, ['alarms', 'active_alarms'])),
            ],
            'AlertsChart' => $this->alertTypeSeries($snapshotData),
            'ServiceLevelChart' => [
                'periodical_calls' => $this->intValue($this->path($snapshotData, ['service_level', 'periodical_calls'])),
                'local_checks' => $this->intValue($this->path($snapshotData, ['service_level', 'local_checks'])),
            ],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    public function supportedAlertTypes(): array
    {
        return self::ALERT_TYPES;
    }

    /**
     * @return array<string, int>
     */
    private function alertTypeSeries(array $snapshotData): array
    {
        $alerts = $this->path($snapshotData, ['alerts', 'alert_type']);
        $series = [];
        foreach (self::ALERT_TYPES as $type) {
            $legacyValue = is_array($alerts) ? ($alerts[$type] ?? null) : null;
            $rawValue = is_array($alerts) ? ($alerts[self::ALERT_TYPE_MAP[$type] ?? ''] ?? null) : null;
            $series[$type] = $this->intValue($legacyValue ?? $rawValue ?? 0);
        }

        return $series;
    }

    private function path(array $data, array $path): mixed
    {
        $value = $data;
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function intValue(mixed $value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return (int) round((float) $value);
    }
}
