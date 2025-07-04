<?php
// app/Events/ReverbServerStatusUpdated.php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
//事件要控制即時發送或佇列發送廣播
// class ReverbServerStatusUpdated implements ShouldBroadcast
class ReverbServerStatusUpdated implements ShouldBroadcastNow{
    use InteractsWithSockets,Dispatchable, SerializesModels;
    public $status;
    public function __construct($status){
        $this->status = $status;
    }

    public function broadcastOn(){
        return new Channel('channel-Reverb');
    }

    public function broadcastAs(){
        return 'event-Reverb';
    }

    public function broadcastWith(){
        return [
            'message' => $this->status,
            'time' => now()->toDateTimeString()
        ];
    }
}



