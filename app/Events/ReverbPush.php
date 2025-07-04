<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReverbPush implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channelName;
    public $eventName;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param string $channelName 廣播頻道名稱（例如 'reverbpush'）
     * @param string $eventName 廣播事件名稱（例如 'reverbpush.updated'）
     * @param array $data 推送的資料（例如 Python 傳來的資料）
     */
    public function __construct($channelName, $eventName, $data)
    {
        $this->channelName = $channelName;
        $this->eventName = $eventName;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel($this->channelName);
    }

    /**
     * Get the event name for broadcasting.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return $this->eventName;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'data' => $this->data,
            'timestamp' => now()->toDateTimeString()
        ];
    }
}
