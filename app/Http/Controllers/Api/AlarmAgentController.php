<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Session;
use App\Services\SettingsService;
use App\Traits\FreeswitchApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlarmAgentController extends Controller
{
    use FreeswitchApiTrait;

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Connect an agent to an alarm call via Freeswitch
     * Secure endpoint - requires Auth + agent role
     */
    public function connectAgent(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer'
        ]);

        $deviceId = $request->input('device_id');
        $user = Auth::user();

        // Check user has agent role
        if (!$user->isAgent) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized - agent role required'
            ], 403);
        }

        if (!$user->user_ext) {
            return response()->json([
                'success' => false,
                'error' => 'User extension not found'
            ], 400);
        }

        try {
            // Get device with relations
            $device = Device::with('device_site')->findOrFail($deviceId);
            $deviceAccountId = $device->device_account_id;

            // Check user has access to device's account
            $userHasAccess = DB::table('users_roles')
                ->where('ur_user_id', $user->user_id)
                ->where(function($query) use ($deviceAccountId) {
                    $query->where('ur_account_id', $deviceAccountId)
                        ->orWhereNull('ur_account_id'); // Site role has access to all accounts
                })
                ->exists();

            if (!$userHasAccess) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized - no access to this device account'
                ], 403);
            }

            // Find active ALARM session for this device (same logic as alarm notification)
            $alarmSession = Session::with('session_type')
                ->where('session_device_id', $deviceId)
                ->whereHas('session_type', function($query) {
                    $query->whereRaw("BINARY st_type = BINARY 'ALARM'");
                })
                ->whereNull('session_end')
                ->first();

            if (!$alarmSession) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active ALARM session found for this device'
                ], 404);
            }

            // Find the currently active AGENT session (not ended).
            // Earlier routes may have ended (session_end set) without being answered,
            // so we must filter for the active one and take the most recently started.
            $agentSession = Session::with('session_type')
                ->where('session_ref_id', $alarmSession->session_id)
                ->whereHas('session_type', function($query) {
                    $query->whereRaw("BINARY st_type = BINARY 'AGENT'");
                })
                ->whereNull('session_end')
                ->latest('session_start')
                ->first();

            if (!$agentSession) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active AGENT session found for this alarm'
                ], 400);
            }

            $deviceSite = $device->device_site;
            $siteSettings = $this->settingsService->getPlainSiteSettings($deviceSite);

            if ($this->isLiftcareAgentQueue($alarmSession, $siteSettings)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Agent connection not available for liftcare agent queue'
                ], 403);
            }

            $cliNumber = $siteSettings['call.alarm.route1.cli.number']['value']
                ?? $siteSettings['call.alarm.route2.cli.number']['value']
                ?? $siteSettings['call.alarm.route3.cli.number']['value']
                ?? $siteSettings['call.outbound.trunk.cli.number']['value']
                ?? $siteSettings['call.trunk.cli.number']['value']
                ?? '1234567';

            $alarmSessionUuid = $alarmSession->session_uuid;

            $fsCommand = sprintf(
                "bgapi originate {execute_on_answer=start_dtmf_generate,ignore_early_media=true,hangup_after_bridge=true,origination_caller_id_name=UCP,origination_caller_id_number=%s}sofia/gateway/peoplefone/%s %s",
                $cliNumber,
                $user->user_ext,
                $alarmSessionUuid
            );

            Log::info('AlarmAgentController - Connecting agent to alarm', [
                'device_id' => $deviceId,
                'user_id' => $user->user_id,
                'user_ext' => $user->user_ext,
                'alarm_session_uuid' => $alarmSessionUuid,
                'alarm_session_id' => $alarmSession->session_id,
                'agent_session_uuid' => $agentSession->session_uuid,
                'cli_number' => $cliNumber,
                'fs_command' => $fsCommand
            ]);

            // Execute Freeswitch command on ucp-agent host
            // todo: "ucp-agent" HAS TO BE CONFIGURABLE AND AGAIN - ADD DOMAIN AS A PARAM
            $result = $this->fsMake($fsCommand, false, false, config('app.agent_server'));

            if ($result) {
                Log::info('AlarmAgentController - Agent connection initiated successfully', [
                    'device_id' => $deviceId,
                    'user_id' => $user->user_id,
                    'fs_response' => $result
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Agent connection initiated',
                    'alarm_session_uuid' => $alarmSessionUuid
                ]);
            } else {
                Log::error('AlarmAgentController - Freeswitch command failed', [
                    'device_id' => $deviceId,
                    'user_id' => $user->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Freeswitch command failed'
                ], 503);
            }

        } catch (\Throwable $e) {
            Log::error('AlarmAgentController - Connect agent failed', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Classify an alarm call - send classification to Freeswitch
     * Secure endpoint - requires Auth + agent role
     */
    public function classifyAlarm(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'alarm_session_uuid' => 'required|string',
            'classification' => 'required|string'
        ]);

        $deviceId = $request->input('device_id');
        $alarmSessionUuid = $request->input('alarm_session_uuid');
        $classification = $request->input('classification');
        $user = Auth::user();

        // Check user has agent role
        if (!$user->isAgent) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized - agent role required'
            ], 403);
        }

        try {
            $device = Device::with('device_site')->findOrFail($deviceId);

            // Find ALARM session
            $alarmSession = Session::with('session_type')
                ->where('session_uuid', $alarmSessionUuid)
                ->whereHas('session_type', function($query) {
                    $query->whereRaw("BINARY st_type = BINARY 'ALARM'");
                })
                ->whereNull('session_end')
                ->first();

            if (!$alarmSession) {
                return response()->json([
                    'success' => false,
                    'error' => 'ALARM session not found or already ended'
                ], 404);
            }

            $siteSettings = $this->settingsService->getPlainSiteSettings($device->device_site);

            if ($this->isLiftcareAgentQueue($alarmSession, $siteSettings)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Classification not available for liftcare agent queue'
                ], 403);
            }

            $host = $alarmSession->session_host;

            $fsCommand = sprintf(
                "ucp classify call %s %s",
                $alarmSessionUuid,
                $classification
            );

            Log::info('AlarmAgentController - Classifying alarm call', [
                'device_id' => $deviceId,
                'user_id' => $user->user_id,
                'alarm_session_uuid' => $alarmSessionUuid,
                'classification' => $classification,
                'fs_command' => $fsCommand,
                'host' => $host
            ]);

            $result = $this->fsMake($fsCommand, false, false, $host);

            if ($result) {
                Log::info('AlarmAgentController - Classification command executed successfully', [
                    'device_id' => $deviceId,
                    'user_id' => $user->user_id,
                    'classification' => $classification
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Classification command processed'
                ]);
            } else {
                Log::error('AlarmAgentController - Classification command failed', [
                    'device_id' => $deviceId,
                    'user_id' => $user->user_id,
                    'classification' => $classification
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Freeswitch classification command failed'
                ], 503);
            }

        } catch (\Throwable $e) {
            Log::error('AlarmAgentController - Classification failed', [
                'device_id' => $deviceId,
                'classification' => $classification,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function isLiftcareAgentQueue(Session $alarmSession, array $siteSettings): bool
    {
        $alarmSession->loadMissing('agentSessions');
        $endedAgentCount = $alarmSession->agentSessions->whereNotNull('session_end')->count();
        $currentRoute    = min($endedAgentCount + 1, 3);
        $routeTrunk      = $siteSettings["call.alarm.route{$currentRoute}.trunk"]['value'] ?? null;

        return $routeTrunk === 'gateway/liftcare_agent';
    }
}
