<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // 實現這個接口
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MachineDataCapturedEvent implements ShouldBroadcast // 實現 ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastData; // 公共屬性會被廣播

    /**
     * 創建一個新的事件實例。
     *
     * @param array $broadcastData 包含需要廣播的數據
     * @return void
     */
    public function __construct(array $broadcastData)
    {
        $this->broadcastData = $broadcastData;
    }

    /**
     * 獲取事件應該廣播到的 Channel。
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]
     */
    public function broadcastOn()
    {
        // 這裡定義廣播到的頻道名稱
        // 可以根據業務需求廣播到一個公共頻道或私有頻道
        // 例如，廣播到一個公共頻道 'machine-data'
        return new Channel('machine-data');

        // 如果是私有頻道，例如按店舖或機器區分
        // return new PrivateChannel('machine-data.' . $this->broadcastData['chip_id']);
    }

    /**
     * 廣播事件時獲取的數據。
     *
     * @return array
     */
    public function broadcastWith()
    {
        // 返回需要廣播給前端的數據
        return $this->broadcastData;
    }

    /**
     * 廣播事件名稱 (可選，如果未定義，默認是 Event 的類名)
     *
     * @return string
     */
    // public function broadcastAs()
    // {
    //     return 'machines.data.captured'; // 前端監聽時使用的事件名稱
    // }
}
