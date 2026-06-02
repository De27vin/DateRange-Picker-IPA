<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealtimeEventBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $eventType;
    public array $data;
    public int $accountId;
    public string $timestamp;

    /**
     * Create a new event instance.
     *
     * @param string $eventType Event type identifier (e.g. 'alarm_notification', 'carcall_status', 'agent_status')
     * @param int $accountId Account ID for channel routing
     * @param array $data Event-specific payload data
     */
    public function __construct(string $eventType, int $accountId, array $data)
    {
        $this->eventType = $eventType;
        $this->accountId = $accountId;
        $this->data = $data;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('realtime.account.' . $this->accountId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'realtime-event';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'event_type' => $this->eventType,
            'data' => $this->data,
            'timestamp' => $this->timestamp
        ];
    }
}
