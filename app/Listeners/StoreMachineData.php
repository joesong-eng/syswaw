<?php

namespace App\Listeners;

use App\Events\MachineDataReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis; // 引入 Redis Facade
use Illuminate\Support\Facades\Log; // 確保 Log Facade 已引入

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
        $mqttData = $event->data;

        // 確保數據中包含 chip_id
        if (!isset($mqttData['chip_id'])) {
            Log::warning('Received MQTT data without chip_id.', ['data' => $mqttData]);
            return;
        }

        $chipId = $mqttData['chip_id'];
        $redisKey = 'machine_data:' . $chipId;

        // 從 Redis 獲取舊數據
        $oldDataJson = Redis::get($redisKey);
        $oldData = $oldDataJson ? json_decode($oldDataJson, true) : null;

        // 準備用於比較的數據（排除 timestamp）
        $currentComparableData = $mqttData;
        unset($currentComparableData['timestamp']);

        $oldComparableData = $oldData;
        if ($oldComparableData && isset($oldComparableData['timestamp'])) {
            unset($oldComparableData['timestamp']);
        }

        // 比較新舊數據，如果不同則更新 Redis
        if ($oldData === null || $currentComparableData !== $oldComparableData) {
            // 將完整的新數據（包含時間戳）儲存到 Redis
            Redis::set($redisKey, json_encode($mqttData));
            Log::info('Machine data updated in Redis.', ['chip_id' => $chipId, 'data' => $mqttData]);
        } else {
            Log::info('Machine data unchanged, skipping Redis update.', ['chip_id' => $chipId]);
        }

        // 移除直接寫入資料庫的邏輯，這將由排程任務處理
        // try {
        //     MachineData::create([
        //         'chip_id' => $mqttData['chip_id'],
        //         'data' => json_encode($mqttData['data']),
        //     ]);
        //     \Log::info('Machine data stored successfully.', ['chip_id' => $mqttData['chip_id']]);
        // } catch (\Exception $e) {
        //     \Log::error('Failed to store machine data: ' . $e->getMessage(), ['data' => $mqttData]);
        // }
    }
}
