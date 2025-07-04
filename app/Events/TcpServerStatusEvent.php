<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
// use Illuminate\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TcpServerStatusEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public $status;
    public $action;
    public $timestamp;
    public $error;

    public function __construct(array $data)
    {
        $this->status = $data['status'];
        $this->action = $data['action'];
        $this->timestamp = $data['timestamp'];
        $this->error = $data['error'] ?? null;
    }

    public function broadcastOn()
    {
        return new Channel('tcp_server_status');
    }

    public function broadcastAs()
    {
        return 'TcpServerStatusEvent';
    }

    public function broadcastWith()
    {
        return [
            'status' => $this->status,
            'action' => $this->action,
            'timestamp' => $this->timestamp,
            'error' => $this->error,
        ];
    }
}
