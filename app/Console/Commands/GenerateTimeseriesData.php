<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTimeseriesData extends Command
{
    protected $signature = 'timeseries:generate {hours=48}';
    protected $description = 'Generate randomized time-series snapshots for testing';

    public function handle()
    {
        $hours = (int) $this->argument('hours');

        for ($i = $hours; $i >= 0; $i--) {

            $timestamp = now()->subHours($i)->startOfHour()->toDateTimeString();

            $data = [
                'enabled' => rand(30, 120),
                'disabled' => rand(0, 30),

                'inbound_calls' => rand(0, 50),
                'active_alarms' => rand(0, 100),

                'periodic_checks' => rand(0, 100),
                'local_checks' => rand(0, 100),

                'Active_alarm' => rand(0, 50),
                'Battery_malfunction' => rand(0, 50),
                'Battery_low' => rand(0, 50),
                'Button_malfunction' => rand(0, 50),
                'Charge_malfunction' => rand(0, 50),
                'Database_malfunction' => rand(0, 50),
                'Disk_low' => rand(0, 50),
                'Object_door_failure' => rand(0, 50),
                'Elevator_failure' => rand(0, 50),
                'Gateway_malfunction' => rand(0, 50),
                'Identity_mismatch' => rand(0, 50),
                'Object_is_not_level' => rand(0, 50),
                'Light_malfunction' => rand(0, 50),
                'Line_alarm' => rand(0, 50),
                'Location_alarm' => rand(0, 50),
                'Object_is_under_maintenance' => rand(0, 50),
                'Microphone_malfunction' => rand(0, 50),
                'Network_malfunction' => rand(0, 50),
                'Periodical_call_overdue' => rand(0, 50),
                'PIN_mismatch' => rand(0, 50),
                'Power_malfunction' => rand(0, 50),
                'RAM_low' => rand(0, 50),
                'Reserved_device' => rand(0, 50),
                'Serial_port_malfunction' => rand(0, 50),
                'Shaft_failure' => rand(0, 50),
                'Low_signal' => rand(0, 50),
                'SIP_registration_failure' => rand(0, 50),
                'Speaker_malfunction' => rand(0, 50),
                'Technician_check_overdue' => rand(0, 50),
                'Voice_alarm' => rand(0, 50),
            ];

            DB::table('timeseries')->updateOrInsert(
                ['ts_timestamp' => $timestamp],
                ['ts_data' => json_encode($data, JSON_UNESCAPED_UNICODE)]
            );
        }

        $this->info("Generated snapshots for the last {$hours} hours.");
        return 0;
    }
}
