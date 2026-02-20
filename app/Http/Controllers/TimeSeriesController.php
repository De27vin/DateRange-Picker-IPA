<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeSeriesController extends Controller
{
    public function fetch(Request $request)
    {
        $hours = (int) $request->input('hours', 500);

        $rows = DB::table('timeseries')
            ->where('ts_timestamp', '>=', now()->subHours($hours))
            ->orderBy('ts_timestamp', 'asc')
            ->get();

        $result = $rows->map(function ($row) {
            $d = json_decode($row->ts_data, true);

return [
    'timestamp' => $row->ts_timestamp,

    // Equipment
    'enabled' => $d['enabled'] ?? 0,
    'disabled' => $d['disabled'] ?? 0,

    // Alarms
    'inbound_calls' => $d['inbound_calls'] ?? 0,
    'active_alarms' => $d['active_alarms'] ?? 0,

    // Alerts
    'Active_alarm' => $d['Active_alarm'] ?? 0,
    'Battery_malfunction' => $d['Battery_malfunction'] ?? 0,
    'Battery_low' => $d['Battery_low'] ?? 0,
    'Button_malfunction' => $d['Button_malfunction'] ?? 0,
    'Charge_malfunction' => $d['Charge_malfunction'] ?? 0,
    'Database_malfunction' => $d['Database_malfunction'] ?? 0,
    'Disk_low' => $d['Disk_low'] ?? 0,
    'Object_door_failure' => $d['Object_door_failure'] ?? 0,
    'Elevator_failure' => $d['Elevator_failure'] ?? 0,
    'Gateway_malfunction' => $d['Gateway_malfunction'] ?? 0,
    'Identity_mismatch' => $d['Identity_mismatch'] ?? 0,
    'Line_alarm' => $d['Line_alarm'] ?? 0,
    'Location_alarm' => $d['Location_alarm'] ?? 0,
    'Object_is_under_maintenance' => $d['Object_is_under_maintenance'] ?? 0,
    'Microphone_malfunction' => $d['Microphone_malfunction'] ?? 0,
    'Network_malfunction' => $d['Network_malfunction'] ?? 0,
    'Periodical_call_overdue' => $d['Periodical_call_overdue'] ?? 0,
    'Pin_mismatch' => $d['Pin_mismatch'] ?? 0,
    'Power_malfunction' => $d['Power_malfunction'] ?? 0,
    'Ram_low' => $d['Ram_low'] ?? 0,
    'Reserved_device' => $d['Reserved_device'] ?? 0,
    'Serial_port_malfunction' => $d['Serial_port_malfunction'] ?? 0,
    'Shaft_failure' => $d['Shaft_failure'] ?? 0,
    'Low_signal' => $d['Low_signal'] ?? 0,
    'Sip_registration_failure' => $d['Sip_registration_failure'] ?? 0,
    'Speaker_malfunction' => $d['Speaker_malfunction'] ?? 0,
    'Technician_check_overdue' => $d['Technician_check_overdue'] ?? 0,
    'Voice_alarm' => $d['Voice_alarm'] ?? 0,

    // Service Levels
    'periodic_checks' => $d['periodic_checks'] ?? 0,
    'local_checks' => $d['local_checks'] ?? 0,
];

        });

        return response()->json($result);
    }
}
