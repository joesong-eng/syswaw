<?php
// app/Events/TcpServerState.php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class TcpServerState implements ShouldBroadcastNow{
    use InteractsWithSockets, Dispatchable, SerializesModels;
    public $status;
    public $action; // 新增 action 屬性

    public function __construct($status, $action = null){ // 修改建構子以接收 action
        $this->status = $status;
        $this->action = $action; // 設定 action 屬性
    }

    public function broadcastOn(){
        return new Channel('redis-status-channel');
    }

    public function broadcastAs(){
        return 'tcp.status.updated';
    }

    public function broadcastWith(){
        return [
            'status' => $this->status,
            'action' => $this->action, // 將 action 加入廣播資料
            'time' => now()->toDateTimeString()
        ];
    }
}
