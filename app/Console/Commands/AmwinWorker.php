<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\DeviceAlertsService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AmwinWorker extends Command
{
    protected $signature = 'amwin:worker';
    protected $description = 'Handles alarm calls for elevators by sending notifications to Amwin.';

    private string $amwinBaseUrl;
    private array $trackedAlerts = [];
    private Client $client;
    private DeviceAlertsService $alertsService;
    private int $sleepInterval;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
        $this->alertsService = new DeviceAlertsService();
    }

    public function handle(): void
    {
        $this->amwinBaseUrl = env('AMWIN_BASE_URL');
        if (empty($this->amwinBaseUrl)) {
            throw new RuntimeException('AMWIN_BASE_URL configuration is missing');
        }

        $amwinAccountId = Account::where('account_slug', 'liftcare')->value('account_id');
        if (!$amwinAccountId) {
            throw new \Exception('Amwin account not found');
        }

        $this->sleepInterval = (int) env('AMWIN_WORKER_INTERVAL', 5);
        $this->initializeTrackedAlerts($amwinAccountId);

        while (true) {
            $this->processAlerts($amwinAccountId);
            sleep($this->sleepInterval);
        }
    }

    private function initializeTrackedAlerts(int $accountId): void
    {
        $alerts = $this->alertsService->getCurrentAlertsForAccount($accountId);
        $this->printAlerts('INIT', $alerts);

        foreach ($alerts as $alert) {
            $this->trackedAlerts[$alert->da_id] = $alert;
        }
    }

    private function processAlerts(int $accountId): void
    {
        $alerts = $this->alertsService->getCurrentAlertsForAccount($accountId);
        $this->printAlerts('CURR', $alerts);

        $currentAlertIds = $alerts->pluck('da_id')->toArray();

        $this->processRemoveAlerts($currentAlertIds);
        $this->processAddAlerts($alerts);
    }

    private function processRemoveAlerts(array $currentAlertIds): void
    {
        foreach ($this->trackedAlerts as $alertId => $alert) {
            if (in_array($alertId, $currentAlertIds)) {
                continue;
            }

            $endpoint = ($alert->at_type === 'VOICE') ? 'alarm' : 'alert';
            $payload = [
                'EQID'      => $alert->device_equipment,
                'Timestamp' => now()->toIso8601String(),
            ];

            if ($endpoint === 'alert') {
                $payload['AlertType'] = $alert->at_type;
                $payload['OptValue']  = $alert->da_value;
                $payload['State']     = "false";
            }

            if ($endpoint === 'alarm') {
                $this->info("REMOVE SUCCESS on alarm endpoint for alert {$alertId} (no payload sent)");
                unset($this->trackedAlerts[$alertId]);
                continue;
            }

            try {
                $this->info("REMOVE SUCCESS on {$endpoint} endpoint for alert {$alertId}");
                $this->sendRequest($endpoint, $payload);
            } catch (\Exception $e) {
                Log::error("Failed to send end state for alert {$alertId}: " . $e->getMessage());
            }

            unset($this->trackedAlerts[$alertId]);
        }
    }

    private function processAddAlerts($alerts): void
    {
        foreach ($alerts as $alert) {
            if (isset($this->trackedAlerts[$alert->da_id])) {
                continue;
            }

            $endpoint = ($alert->at_type === 'VOICE') ? 'alarm' : 'alert';
            $payload = [
                'EQID'      => $alert->device_equipment,
                'Timestamp' => Carbon::createFromTimeString($alert->da_timestamp)->toIso8601String(),
            ];

            if ($endpoint === 'alarm') {
                $payload['UUID'] = $alert->session_uuid;
            } else {
                $payload['AlertType'] = $alert->at_type;
                $payload['OptValue']  = $alert->da_value;
                $payload['State']     = "true";
            }

            try {
                $this->info("ADD SUCCESS on {$endpoint} endpoint for alert {$alert->da_id}");
                $response = $this->sendRequest($endpoint, $payload);
                if ($response && $response->getStatusCode() === 200) {
                    $this->trackedAlerts[$alert->da_id] = $alert;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send alert {$alert->da_id}: " . $e->getMessage());
            }
        }
    }

    private function sendRequest(string $endpoint, array $payload)
    {
        return $this->client->post("{$this->amwinBaseUrl}/{$endpoint}", [
            'json'    => $payload,
            'headers' => ['Content-Type' => 'application/json'],
            'auth'    => ['UCPTest', 'UCPPass'],
        ]);
    }

    private function printAlerts(string $label, $alerts): void
    {
        $this->info($label);
        $headers = ['da_id', 'at_type', 'da_value', 'session_uuid', 'device_equipment', 'da_timestamp'];
        $tableData = $alerts->map(function ($alert) {
            return [
                $alert->da_id,
                $alert->at_type,
                $alert->da_value,
                $alert->session_uuid,
                $alert->device_equipment,
                $alert->da_timestamp,
            ];
        })->toArray();
        $this->table($headers, $tableData);
    }

}
