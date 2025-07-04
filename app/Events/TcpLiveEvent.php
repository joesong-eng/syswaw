<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TcpLiveEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 從 Python 接收到的原始數據 (可能是單個物件或物件陣列)
     * @var mixed
     */
    public $rawData;

    /**
     * Create a new event instance.
     *
     * @param mixed $rawData
     * @return void
     */
    public function __construct($rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // **** 定義一個新的頻道名稱 直接給前端window.ECHO ****
        return new Channel('tcpLiveEvent');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        // **** 定義一個新的事件名稱 ****
        return 'DataReceived';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        // 將原始數據包裝在 'data' 鍵下，並附上伺服器時間戳
        return [
            'data' => $this->rawData,
            'timestamp' => now()->toIso8601String() // 使用 ISO 8601 格式的時間戳
        ];
    }
}
