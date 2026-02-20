<?php
// app/Console/Commands/ConsumeStompQueue.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stomp\Client;
use Stomp\Network\Connection;
use Stomp\StatefulStomp;
use Stomp\Transport\Frame;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class ConsumeStompQueue extends Command
{
    protected $signature   = 'rabbitmq:stomp-consume {queue : sessions|alerts|events|sets}';
    protected $description = 'Consume a single RabbitMQ STOMP queue';

    private StatefulStomp $stomp;
    private string        $queue;

    /* ------------------------------------------------------------------ */
    public function handle(): void
    {
        try {
            $this->queue = '/queue/'.$this->argument('queue');
            $this->connect();
            $this->subscribe();
            $this->listen();
        } catch (\Throwable $e) {
            Log::error("STOMP - worker failed: {$e->getMessage()}");
        }
    }

    /* ------------------------------------------------------------------ */
    private function connect(): void
    {
        try {
            $conn = new Connection(
                sprintf('tcp://%s:%s',
                    config('rabbitmq.host', 'localhost'),
                    config('rabbitmq.port', 61613))
            );

            $client = new Client($conn);
            $client->setLogin(config('rabbitmq.user'), config('rabbitmq.password'));
            $client->setVhostname(config('rabbitmq.vhost', 'ucp'));
            $client->setClientId("{$this->argument('queue')}-worker-".uniqid());
            $client->setHeartbeat(30000, 30000);
            $client->connect();

            $this->stomp = new StatefulStomp($client);
        } catch (\Throwable $e) {
            Log::error("STOMP - connect failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /* ------------------------------------------------------------------ */
    private function subscribe()
    {
        try {
            $this->stomp->subscribe(
                $this->queue, // destination
                null, // selector  (you don’t need one)
                'client-individual', // manual ACK mode
                [
                    'id' => uniqid('sub-'), // subscription id
                    'prefetch-count' => 1, // QoS
                ]
            );

            $this->info("Listening on {$this->queue}");
        } catch (\Throwable $e) {
            Log::error("STOMP - subscribe failed: {$e->getMessage()}");
            $this->error("STOMP - subscribe failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /* ------------------------------------------------------------------ */
    private function listen()
    {
        while (true) {
            try {
                $frame = $this->stomp->read();
                if ($frame) {
                    $this->handleMessage($frame);
                }
            } catch (\Throwable $e) {
                Log::error("STOMP - frame read failed: {$e->getMessage()}");
                $this->error("STOMP - frame read failed: {$e->getMessage()}");
                $this->reconnect();
            }
        }
    }

    /* ------------------------------------------------------------------ */
    private function handleMessage(Frame $frame): void
    {
        try {
            $data = json_decode($frame->body, true, flags: JSON_THROW_ON_ERROR);

            match ($this->queue) {
                '/queue/sessions' => $this->handleSession($data),
                '/queue/alerts'   => $this->handleAlert($data),
                '/queue/events'   => $this->handleEvent($data),
                '/queue/sets'     => $this->handleSet($data),
            };

            $this->stomp->ack($frame);
        } catch (\Throwable $e) {
            Log::error("STOMP - message processing failed: {$e->getMessage()}", ['body' => $frame->body]);
            $this->error("STOMP - message processing failed: {$e->getMessage()}");
            $this->stomp->nack($frame);   // re‑queue
        }
    }
    /* ------------------------------------------------------------------ */
    private function handleSession(array $data): void
    {
        if (!isset($data['session'], $data['type'], $data['direction'], $data['paths'], $data['timestamp'])) {
            throw new \RuntimeException('Invalid session payload');
        }

//        Cache::put("session:{$data['session']}", ['account_id' => $data['account_id'] ?? null, 'device_id' => $data['device_id'] ?? null], 7200);

        $this->info('SESSION: '.json_encode($data));
        Log::info('SESSION', $data);
    }
    /* ------------------------------------------------------------------ */
    private function handleAlert(array $data): void
    {
        // basic validation
        if (!isset($data['session'], $data['type'], $data['state'], $data['timestamp'])) {
            throw new \RuntimeException('Invalid alert payload');
        }

        // we only care about TECH | ACTIVE
        if ($data['type'] !== 'TECH' || $data['state'] !== 'ACTIVE') {
            return;
        }

        /* ------------------------------------------------------------------ */
//        $session = Cache::remember("session:{$data['session']}", 7200, function () use ($data) {
//            return DB::table('sessions as s')
//                ->leftJoin('devices as d', 'd.device_id', '=', 's.session_device_id')
//                ->select('s.session_account_id as account_id', 'd.device_equipment as equipment')
//                ->where('s.session_uuid', $data['session'])
//                ->first();
//        });

//        echo $data['session'];
//
//        $session = DB::table('sessions as s')
//            ->leftJoin('devices as d', 'd.device_id', '=', 's.session_device_id')
//            ->select('s.session_account_id as account_id', 'd.device_equipment as equipment')
//            ->where('s.session_uuid', $data['session'])
//            ->first();
//
//        // IMPORTANT CONVERSION
//        if (!empty($session) && is_array($session)) { $session = (object) $session; }
//
//        if (empty($session?->account_id)) {
//            Log::warning('TECH alert – session not found', $data);
//            return;
//        }

        /* ------------------------------------------------------------------ */
//        $emails = Cache::remember("account_emails:{$session->account_id}", 86400, function () use ($session) {
//            return DB::table('accounts_emails as ae')
//                ->join('emails as e', 'e.email_id', '=', 'ae.ae_email_id')
//                ->where('ae.ae_account_id', $session->account_id)
//                ->pluck('e.email_address')
//                ->all();
//        });

        $emails = array_merge([], ['alejandro.monje@serv24.com', 'benjamin.vogt@serv24.com', 'jacek.dziurdzikowski@serv24.com']);

//        if (empty($emails)) {
//            Log::info('TECH alert – no e‑mails configured', ['account_id' => $session->account_id]);
//            return;
//        }

        /* ------------------------------------------------------------------ */
        $body = "Local checks failed for:\n"
            . "+---------------------+------------+\n"
            . "| timestamp           | equipment  |\n"
            . "+---------------------+------------+\n"
//            . "| {$data['timestamp']} | {$session->equipment} |\n"
            . "+---------------------+------------+\n";

        /* ------------------------------------------------------------------ */

//        $this->info('sending emails to:');
//        $this->table(['emails'], $emails);
//        Log::info('sending emails to:', $emails);

        Mail::raw($body, fn ($m) => $m
            ->to($emails)
            ->subject('Local Check Alert – ACTIVE'));

        $this->info('Emails sent');
        Log::info('Emails sent');
    }
    /* ------------------------------------------------------------------ */
    private function handleEvent(array $data): void
    {
        $this->info('EVENT: '.json_encode($data));
        Log::info('EVENT', $data);
    }
    /* ------------------------------------------------------------------ */
    private function handleSet(array $data): void
    {
        $this->info('SET', $data);
        Log::info('SET', $data);
    }
    /* ------------------------------------------------------------------ */
    private function reconnect(): void
    {
        try { $this->stomp?->disconnect(); } catch (\Throwable) {}
        sleep(5);
        $this->connect();
        $this->subscribe();
    }
}
