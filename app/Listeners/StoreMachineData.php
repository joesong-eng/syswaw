<?php

namespace App\Listeners;

use App\Events\MachineDataReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\MachineData; // 引入 MachineData 模型

class StoreMachineData
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MachineDataReceived $event): void
    {
        // 假設 $event->data 包含 'chip_id' 和 'data' 鍵
        // 並且 'data' 是一個包含機器數據的陣列或物件
        $mqttData = $event->data;

        // 這裡需要根據實際的 MachineData 模型和 MQTT 數據結構進行調整
        // 假設 MQTT 數據中的 'data' 鍵包含所有需要儲存的欄位
        // 例如：{"chip_id": "ABC", "data": {"sensor_temp": 25, "humidity": 60}}
        // 或者：{"chip_id": "ABC", "data": "sensor_temp=25&humidity=60"}

        // 為了簡化，我們假設 $mqttData['data'] 是一個包含所有數據的陣列
        // 如果 $mqttData['data'] 是一個字串，您可能需要進一步解析它
        try {
            MachineData::create([
                'chip_id' => $mqttData['chip_id'],
                'data' => json_encode($mqttData['data']), // 將數據儲存為 JSON 字串
                // 根據您的 MachineData 表結構添加其他欄位
                // 例如 'temperature' => $mqttData['data']['sensor_temp'],
                // 'humidity' => $mqttData['data']['humidity'],
            ]);
            \Log::info('Machine data stored successfully.', ['chip_id' => $mqttData['chip_id']]);
        } catch (\Exception $e) {
            \Log::error('Failed to store machine data: ' . $e->getMessage(), ['data' => $mqttData]);
        }
    }
}
