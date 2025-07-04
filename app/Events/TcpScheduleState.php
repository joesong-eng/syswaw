<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // 使用 ShouldBroadcastNow
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TcpScheduleState implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $status;
    public ?int $interval;
    public ?string $error;

    /**
     * Create a new event instance.
     */
    public function __construct(string $status, ?int $interval, ?string $error = null) // 添加 $error 参数
    {
        $this->status = $status;
        $this->interval = $interval;
        $this->error = $error; // 现在使用参数中的 $error
    }

    public function broadcastOn(): array
    {
        return [new Channel('reverb_tcpScheduleResponse')];
    }

    public function broadcastAs(): string
    {
        return 'schedule.updated';
    }
}
