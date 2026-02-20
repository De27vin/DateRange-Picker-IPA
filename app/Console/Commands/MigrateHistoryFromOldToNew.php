<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateHistoryFromOldToNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate_history_ucp2_to_ucp21 {afterSessionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Self explanatory';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::disableQueryLog();
        DB::connection('basf_2')->disableQueryLog();
        DB::connection('basf_21')->disableQueryLog();
        set_time_limit(60*60*60);
        $start = microtime(true);

        $afterSessionId = $this->argument('afterSessionId') ?: 0;

        $this->buildMaps();
        $n = 0;
        $allCount = DB::connection('basf_2')->table('sessions')
            ->where('session_id', '>', $afterSessionId)
            ->orderBy('session_id')
            ->count();

        DB::connection('basf_21')->beginTransaction();
        try {
            $sessionsOld = DB::connection('basf_2')->table('sessions')
                ->where('session_id', '>', $afterSessionId)
                ->orderBy('session_id')
                ->lazyById(10000, 'session_id');

            foreach ($sessionsOld as $sessionOld) {
                $n++;
                $sesOld = json_decode(json_encode($sessionOld), true);
                if (!empty($sesOld['session_ref_id'])) { continue; }
                if ($sesOld['session_device_id'] == 741) { continue; }

                $this->insertSession($sesOld);
                unset($sesOld, $sessionOld);

                if ($n % 10000 == 0) {
                    DB::connection('basf_21')->commit();
                    \Log::debug('Finished '.($n).' sessions. Remaining '.($allCount - ($n)).' Percent of completion '.(($n/$allCount)*100).'%');
                    \Log::debug(sprintf("Total time: %s minutes. Memory Used (current): %s. Memory Used (max): %s", round((microtime(true) - $start) / 60, 4), $this->formatBytes(memory_get_usage()), $this->formatBytes(memory_get_peak_usage())));
                    DB::connection('basf_21')->beginTransaction();
                }
            }

            DB::connection('basf_21')->commit();
            echo 'SUCCESS';
        } catch (\Throwable $e) {
            \Log::debug($e, ['MIGRATION ERROR']);
            DB::connection('basf_21')->rollback();
            echo 'INTERRUPTED';
        }
    }

    private function insertSession(array $sessionOld, ?int $refSessionsIdNew = null)
    {
        global $sessionTypesMap;
        global $settingsMap;
        global $alertsMap;
        global $eventsMap;
        global $devicesMap;
        global $pathsMap;
        global $directionsMap;
        global $linesMap;
        global $eventToSettingMap;

        $alertsOld = DB::connection('basf_2')->table('alerts')->where('alert_session_id', $sessionOld['session_id'])->get();
        $eventsOld = DB::connection('basf_2')->table('events')->where('event_session_id', $sessionOld['session_id'])->get();
        $setsOld = DB::connection('basf_2')->table('sets')->where('set_session_id', $sessionOld['session_id'])->get();
        $refSessionsOld = DB::connection('basf_2')->table('sessions')->where('session_ref_id', $sessionOld['session_id'])->get();

        $sessionOld = array_diff_key($sessionOld, ['session_id' => null]);
        $sessionOld['session_ref_id'] = $refSessionsIdNew;
        $sessionOld['session_st_id'] = $sessionTypesMap[$sessionOld['session_st_id']];
        $sessionOld['session_sp_id'] = $pathsMap[$sessionOld['session_sp_id']];
        $sessionOld['session_sd_id'] = $directionsMap[$sessionOld['session_sd_id']];
        $sessionOld['session_device_id'] = $devicesMap[$sessionOld['session_device_id']] ?? null;

        $newSesId = DB::connection('basf_21')->table('sessions')->insertGetId($sessionOld);

        if (!empty($alertsOld)) {
            foreach ($alertsOld as $old) {
                $temp = json_decode(json_encode($old), true);
                $temp['alert_session_id'] = $newSesId;
                $temp['alert_at_id'] = $alertsMap[$old->alert_at_id];
                $temp = array_diff_key($temp, ['alert_id' => null]);
                DB::connection('basf_21')->table('alerts')->insert($temp);
                unset($temp, $old);
            }
        }

        if (!empty($eventsOld)) {
            foreach ($eventsOld as $old) {
                $temp = json_decode(json_encode($old), true);

                if (!empty($eventToSettingMap[$old->event_et_id])) {
                    $set = [
                        'set_session_id' => $newSesId,
                        'set_setting_id' => $eventToSettingMap[$old->event_et_id],
                        'set_value' => $temp['event_value'],
                        'set_success' => 1,
                        'set_timestamp' => $temp['event_timestamp'],
                    ];
                    DB::connection('basf_21')->table('sets')->insert($set);
                    unset($set, $temp, $old);
                    continue;
                }

                $temp['event_session_id'] = $newSesId;
                $temp['event_et_id'] = $eventsMap[$old->event_et_id];

                if (!empty($linesMap[$old->event_et_id])) {
                    $temp['event_value'] = $linesMap[$old->event_et_id];
                }

                $temp = array_diff_key($temp, ['event_id' => null]);
                DB::connection('basf_21')->table('events')->insert($temp);
                unset($temp, $old);
            }
        }

        if (!empty($setsOld)) {
            foreach ($setsOld as $old) {
                $temp = json_decode(json_encode($old), true);
                $temp['set_session_id'] = $newSesId;
                $temp['set_setting_id'] = $settingsMap[$old->set_setting_id];
                $temp = array_diff_key($temp, ['set_id' => null]);
                DB::connection('basf_21')->table('sets')->insert($temp);
                unset($temp, $old);
            }
        }

        if (!empty($refSessionsOld)) {
            foreach ($refSessionsOld as $refOld) {
                $sesRefOld = json_decode(json_encode($refOld), true);
                $this->insertSession($sesRefOld, $newSesId);
                unset($sesRefOld, $refOld);
            }
        }

        unset($newSesId, $sessionOld, $alertsOld, $eventsOld, $setsOld, $refSessionsOld);
        return;
    }


    private function buildMaps ()
    {
        global $sessionTypesMap;
        global $settingsMap;
        global $alertsMap;
        global $eventsMap;
        global $devicesMap;
        global $pathsMap;
        global $directionsMap;
        global $linesMap;
        global $eventToSettingMap;

        $eventToSettingMap = [
            24 => 96, // identity
            26 => 98, // pin
            28 => 97, // module
        ];


        $stMap = ['SYSTEM' => 'MONITOR'];
        $sessionTypesMap = [];
        $stOld = DB::connection('basf_2')->table('session_types')->get();
        $stNew = DB::connection('basf_21')->table('session_types')->get();
        foreach ($stOld as $old) {
            foreach ($stNew as $new) {
                $same = $old->st_type === $new->st_type;
                $modified = !empty($stMap[$old->st_type]) && $stMap[$old->st_type] === $new->st_type;
                if ($same || $modified) {
                    $sessionTypesMap[$old->st_id] = $new->st_id;
                }
            }
        }

        // settings id map
        $settingsMap = [];
        $settingsOld = DB::connection('basf_2')->table('settings')->get();
        $settingsOld = json_decode(json_encode($settingsOld), true);
        $settingsNew = DB::connection('basf_21')->table('settings')->get();
        $settingsNew = json_decode(json_encode($settingsNew), true);
        foreach ($settingsOld as $old) {
            foreach ($settingsNew as $new) {
                $same = $old['setting_key'] === $new['setting_key'];
                $sameTransformed = str_replace('setting.', '', $old['setting_key']);
                $sameTransformed = str_replace('.value', '', $sameTransformed);
                $sameTransformed = $sameTransformed === $new['setting_key'];
                if ($same || $sameTransformed) {
                    $settingsMap[$old['setting_id']] = $new['setting_id'];
                }
            }
        }

        // alert types id map
        $atMap = ['ALARMBTN' => 'BUTTON', 'BATTERY' => 'BATLOW', 'DATABASE' => 'DB'];
        $alertsMap = [];
        $atOld = DB::connection('basf_2')->table('alert_types')->get();
        $atNew = DB::connection('basf_21')->table('alert_types')->get();
        foreach ($atOld as $old) {
            foreach ($atNew as $new) {
                $same = $old->at_type === $new->at_type;
                $modified = !empty($atMap[$old->at_type]) && $atMap[$old->at_type] === $new->at_type;
                if ($same || $modified) {
                    $alertsMap[$old->at_id] = $new->at_id;
                }
            }
        }

        // event types id map
        $etMap = [
            'FWVERSION' => 'FIRMWARE',
            'GETID' => 'IDENTITY',
            'GETMOD' => 'MODULE',
            'GETPIN' => 'PIN',
            'MKPERIODICAL' => 'TIMER',
            'CHKCALL' => 'TRIGGER',
            'RXCHAR' => 'RXTONE',
            'LINE1' => 'LINE',
            'LINE2' => 'LINE',
            'LINE3' => 'LINE',
            'LINE4' => 'LINE',
            'LINE5' => 'LINE',
            'LINE6' => 'LINE',
            'LINE7' => 'LINE',
            'LINE8' => 'LINE',
            'LINE9' => 'LINE',
            'LINE10' => 'LINE',
            'LINE11' => 'LINE',
            'LINE12' => 'LINE',
            'LINE13' => 'LINE',
            'LINE14' => 'LINE',
            'LINE15' => 'LINE',
            'LINE16' => 'LINE',
        ];
        $eventsMap = [];
        $linesMap = [];
        $etOld = DB::connection('basf_2')->table('event_types')->get();
        $etNew = DB::connection('basf_21')->table('event_types')->get();
        foreach ($etOld as $old) {
            foreach ($etNew as $new) {
                $same = $old->et_type === $new->et_type;
                $modified = !empty($etMap[$old->et_type]) && $etMap[$old->et_type] === $new->et_type;
                if ($same || $modified) {
                    if ($new->et_type === 'LINE') {
                        $linesMap[$old->et_id] = $old->et_type;
                    }
                    $eventsMap[$old->et_id] = $new->et_id;
                }
            }
        }

        $devicesMap = [];
        $devOld = DB::connection('basf_2')->table('devices')->get();
        $devNew = DB::connection('basf_21')->table('devices')->get();
        foreach ($devOld as $old) {
            foreach ($devNew as $new) {
                if ($old->device_identity === $new->device_equipment) {
                    $devicesMap[$old->device_id] = $new->device_id;
                }
            }
        }

        $pMap = ['NONE' => 'DATA'];
        $pathsMap = [];
        $pathsOld = DB::connection('basf_2')->table('session_paths')->get();
        $pathsNew = DB::connection('basf_21')->table('session_paths')->get();
        foreach ($pathsOld as $old) {
            foreach ($pathsNew as $new) {
                $same = $old->sp_type === $new->sp_type;
                $modified = !empty($pMap[$old->sp_type]) && $pMap[$old->sp_type] === $new->sp_type;
                if ($same || $modified) {
                    $pathsMap[$old->sp_id] = $new->sp_id;
                }
            }
        }

        $dMap = ['NONE' => 'SYSTEM'];
        $directionsMap = [];
        $dirOld = DB::connection('basf_2')->table('session_directions')->get();
        $dirNew = DB::connection('basf_21')->table('session_directions')->get();
        foreach ($dirOld as $old) {
            foreach ($dirNew as $new) {
                $same = $old->sd_type === $new->sd_type;
                $modified = !empty($dMap[$old->sd_type]) && $dMap[$old->sd_type] === $new->sd_type;
                if ($same || $modified) {
                    $directionsMap[$old->sd_id] = $new->sd_id;
                }
            }
        }

        \Log::debug('BUILT MAPS');
    }


    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
