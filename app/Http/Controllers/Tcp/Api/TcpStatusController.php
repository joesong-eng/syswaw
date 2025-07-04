<?php

namespace App\Http\Controllers\Tcp\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\Machine;
use App\Models\MachineAuthKey;
use App\Models\MachineDataRecord;
use App\Models\BillRecord; // 確保引用 BillRecord
use App\Events\TcpLiveEvent;
use App\Events\TcpServerStatusEvent;
use Carbon\Carbon;

class TcpStatusController extends Controller
{
    public function __construct()
    {
        if (app()->runningInConsole()) {
            $this->subscribeToRedis();
        }
    }

    protected function processData(array $data): array
    {
        $processedData = [];
        //Log::channel('redis_cmd')->info("LARAVEL #3.1# 處理數據開始", ['data' => $data]);
        foreach ($data as $item) {
            $chipHardwareId = $item['chip_hardware_id'] ?? null;
            $authKeyValue = $item['auth_key'] ?? null;
            if (!$chipHardwareId || !$authKeyValue) {
                //Log::channel('redis_cmd')->warning("#3.2# 缺少 chip_hardware_id 或 auth_key", ['item' => $item]);
                continue;
            }
            //Log::channel('redis_cmd')->info("LARAVEL #3.2# 處理 chip_hardware_id={$chipHardwareId}, auth_key={$authKeyValue}");
            // 查詢 MachineAuthKey，確保 chip_hardware_id 和 auth_key 匹配
            $authKey = MachineAuthKey::where('chip_hardware_id', $chipHardwareId)
                ->where('auth_key', $authKeyValue)
                ->first();

            // 利用 machine 關聯取得 machine name
            $machineName = $authKey && $authKey->machine ? $authKey->machine->name : '未知機器';
            $processedData[] = [
                'machine_name'      => $machineName,
                'chip_hardware_id'  => $chipHardwareId,
                'auth_key'          => $authKeyValue,
                'ball_in'           => $item['ball_in'] ?? 0,
                'credit_in'         => $item['credit_in'] ?? 0,
                'ball_out'          => $item['ball_out'] ?? 0,
                'return_value'      => $item['return_value'] ?? 0,
                'assign_credit'     => $item['assign_credit'] ?? 0,
                'settled_credit'    => $item['settled_credit'] ?? 0,
                'bill_denomination' => $item['bill_denomination'] ?? 0,
            ];
            //Log::channel('redis_cmd')->info("LARAVEL #3.3# 處理結果", ['processed' => end($processedData)]);
        }

        //Log::channel('redis_cmd')->info("LARAVEL #3.4# 處理完成", ['total' => count($processedData)]);
        return $processedData;
    }

    protected function subscribeToRedis()
    {
        Log::channel('redis_cmd')->info("LARAVEL subscribeToRedis");
        Redis::publish('tcp_server_cmd', json_encode(['action' => 'start']));
        while (true) {
            try {
                // Log::channel('redis_cmd')->info("LARAVEL #1#[TcpStatusController->subscribeToRedis()] 開始 Redis 訂閱");
                $redis = Redis::connection();
                // Log::channel('redis_cmd')->info("LARAVEL #1.x2# ");

                $pubsub = $redis->pubSubLoop();
                // Log::channel('redis_cmd')->info("LARAVEL #1.x3# ");

                $pubsub->subscribe('tcp_server_status', 'tcp_data_channel');
                $pubsub->subscribe('tcp_server_status', 'tcp_live_channel');
                // Log::channel('redis_cmd')->info("LARAVEL #1.001# channel: ");

                foreach ($pubsub as $message) {
                    try {
                        if ($message->kind !== 'message') {
                            continue;
                        }
                        if ($message->channel === 'tcp_server_status') {
                            $data = json_decode($message->payload, true);
                            if (!$data || !isset($data['status'], $data['action'])) {
                                Log::warning("無效的 tcp_server_status 訊息：{$message->payload}");
                                continue;
                            }
                            $status = $data['status'];
                            $action = $data['action'];
                            $timestamp = $data['timestamp'] ?? Carbon::now()->toDateTimeString();
                            $error = $data['error'] ?? null;
                            // Log::channel('redis_cmd')->info("LARAVEL #2.1#收到 tcp_server_status：status={$status}, action={$action}, timestamp={$timestamp}");
                            event(new TcpServerStatusEvent([
                                'status' => $status,
                                'action' => $action,
                                'timestamp' => $timestamp,
                                'error' => $error
                            ]));
                        } elseif ($message->channel === 'tcp_live_channel') {
                            // Log::channel('redis_cmd')->info("LARAVEL #2.00# 收到 tcp_live_channel {$message->payload}");
                            $data = json_decode($message->payload, true);
                            //Log::channel('redis_cmd')->info("LARAVEL #2.2# 解析後數據", ['data' => $data]);

                            // 統一將數據轉為陣列格式
                            if (isset($data['data']) && is_array($data['data'])) {
                                $data = $data['data'];
                            } elseif (isset($data['chip_hardware_id'], $data['auth_key'])) {
                                $data = [$data]; // 單一物件轉為陣列
                            } else {
                                $data = is_array($data) ? $data : [$data];
                            }

                            // 檢查數據是否有效
                            if (!$data || !isset($data[0]['chip_hardware_id'], $data[0]['auth_key'])) {
                                //Log::channel('redis_cmd')->warning("無效的 tcp_data_channel 訊息：{$message->payload}");
                                continue;
                            }

                            // Log::channel('redis_cmd')->info("LARAVEL #2.21# 收到 tcp_live_channel：chip_hardware_id={$data[0]['chip_hardware_id']}");
                            $processedData = $this->processData($data);
                            // Log::channel('redis_cmd')->info("LARAVEL #2.23# processData 輸出", ['processedData' => $processedData]);

                            if (!empty($processedData)) {
                                event(new TcpLiveEvent($processedData));
                                //Log::channel('redis_cmd')->info("LARAVEL #2.24# 已觸發 TcpLiveEvent 事件", ['data' => $processedData]);
                            } else {
                                //Log::channel('redis_cmd')->warning("LARAVEL #2.24# processedData 為空，未觸發事件");
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("LARAVEL 處理 {$message->channel} 失敗：{$e->getMessage()}", ['payload' => $message->payload]);
                        if (isset($data[0]['chip_hardware_id'])) {
                            $this->processStreamData($data[0]['chip_hardware_id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("LARAVEL 訂閱失敗：{$e->getMessage()}，15 秒後重試...");
                sleep(15);
            }
        }
    }

    protected function processStreamData($chip_hardware_id)
    {
        try {
            $redis = Redis::connection();
            $stream_data = $redis->xRevRange('tcpstream', '+', '-', ['COUNT' => 100]);
            $latest_data = null;
            $ids_to_delete = [];

            foreach ($stream_data as $entry) {
                $fields = $entry[1];
                if ($fields['chip_hardware_id'] === $chip_hardware_id) {
                    if (!$latest_data) {
                        $latest_data = json_decode($fields['data'], true);
                    } else {
                        $ids_to_delete[] = $entry[0];
                    }
                }
            }

            if (!empty($ids_to_delete)) {
                $redis->xDel('tcpstream', $ids_to_delete);
                Log::info("LARAVEL 刪除舊記錄", ['chip_hardware_id' => $chip_hardware_id, 'deleted_ids' => $ids_to_delete]);
            }

            if ($latest_data) {
                $processedData = $this->processData([$latest_data]);
                if (!empty($processedData)) {
                    event(new TcpLiveEvent($processedData));
                    Log::info('LARAVEL 已觸發 TcpLiveEvent 事件', ['data' => $processedData]);
                }
            }
        } catch (\Exception $e) {
            Log::error("LARAVEL 處理 tcpstream 數據失敗：{$e->getMessage()}", ['chip_hardware_id' => $chip_hardware_id]);
        }
    }

    protected function startHeartbeat($redis)
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () use ($redis) {
            try {
                $redis->ping();
                //Log::channel('redis_cmd')->info('LARAVEL Redis 心跳 ping 已發送');
            } catch (\Exception $e) {
                //Log::channel('redis_cmd')->error("LARAVEL Redis 心跳失敗：{$e->getMessage()}");
            }
            pcntl_alarm(30);
        });
        pcntl_alarm(30);
    }

    public function getStatus(Request $request)
    {
        if ($request->header('X-AUTH-TOKEN') !== config('syswaw.tcp_api_key')) {
            Log::info("LARAVEL X-AUTH-TOKEN 未授權", ['ip' => $request->ip()]);
            return response()->json(['message' => '未授權'], 403);
        }

        try {
            $statusData = Redis::get('tcp_server_latest_status');
            $validStatuses = ['running', 'stopped', 'restarting', 'error', 'unknown', 'terminated'];

            if ($statusData) {
                $status = json_decode($statusData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($status)) {
                    $statusValue = $status['status'] ?? 'unknown';
                    if (!in_array($statusValue, $validStatuses)) {
                        Log::warning("無效的 TCP 狀態", ['status' => $statusValue]);
                        $statusValue = 'unknown';
                    }
                    return response()->json([
                        'status' => $statusValue,
                        'action' => $status['action'] ?? null,
                        'timestamp' => $status['timestamp'] ?? Carbon::now()->toDateTimeString(),
                        'error' => $status['error'] ?? null
                    ]);
                }
                Log::warning("LARAVEL tcp_server_latest_status 格式無效", ['data' => $statusData]);
            }

            Log::info("LARAVEL 無 tcp_server_latest_status 記錄");
            return response()->json([
                'status' => 'unknown',
                'action' => null,
                'timestamp' => Carbon::now()->toDateTimeString(),
                'error' => '無最新狀態記錄'
            ]);
        } catch (\Exception $e) {
            Log::error("LARAVEL 獲取 TCP 狀態失敗：{$e->getMessage()}", ['exception' => $e]);
            return response()->json([
                'status' => 'error',
                'action' => null,
                'timestamp' => Carbon::now()->toDateTimeString(),
                'error' => '伺服器錯誤，請檢查 Redis 連線'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        if ($request->header('X-AUTH-TOKEN') !== config('syswaw.tcp_api_key')) {
            Log::info("LARAVEL X-AUTH-TOKEN 未授權");
            return response()->json(['message' => '未授權'], 403);
        }

        $action = $request->input('action');
        if (!in_array($action, ['start', 'stop', 'restart'])) {
            Log::warning("LARAVEL 無效的動作: {$action}");
            return response()->json(['error' => '無效的動作'], 400);
        }

        Redis::publish('tcp_server_cmd', json_encode(['action' => $action]));
        Log::info("LARAVEL Published to tcp_server_cmd: action={$action}");

        return response()->json(['message' => '指令已發送，等待處理']);
    }

    public function dataCaptured(Request $request)
    {
        if ($request->header('X-AUTH-TOKEN') !== config('syswaw.tcp_api_key')) {
            Log::info("LARAVEL X-AUTH-TOKEN 未授權");
            return response()->json(['message' => '未授權'], 403);
        }

        $data = $request->json()->all();
        Log::info("LARAVEL 收到資料:", ['data' => $data]);

        if (empty($data)) {
            Log::error("LARAVEL 接收到空資料");
            return response()->json(['message' => '無效資料'], 400);
        }

        // 統一數據格式為陣列
        $data = is_array($data) && !isset($data['chip_hardware_id']) ? $data : [$data];

        response()->json([
            'message' => '資料已接收',
            'status' => 'success',
            'data_count' => count($data)
        ])->send();

        Log::info('LARAVEL 接收到的資料', [
            'X-AUTH-TOKEN' => $request->header('X-AUTH-TOKEN'),
            'payload' => $data
        ]);

        $processedData = [];
        $recordsWritten = 0;
        $billMappings = config('bill_mappings', []);

        foreach ($data as $item) {
            $chipHardwareId = $item['chip_hardware_id'] ?? null;
            $authKeyValue = $item['auth_key'] ?? null;

            if (!$chipHardwareId || !$authKeyValue) {
                Log::warning("LARAVEL 缺少 chip_hardware_id 或 auth_key", ['item' => $item]);
                continue;
            }

            $authKey = MachineAuthKey::where('chip_hardware_id', $chipHardwareId)
                ->where('auth_key', $authKeyValue)
                ->first();

            if (!$authKey) {
                Log::warning("LARAVEL 未找到 MachineAuthKey", ['chip_hardware_id' => $chipHardwareId, 'auth_key' => $authKeyValue]);
                continue;
            }

            $machine = $authKey->machine_id ? Machine::find($authKey->machine_id) : null;
            $machineName = $machine ? $machine->name ?? '未知機器' : '未知機器';
            $machineType = $machine ? $machine->machine_type : 'unknown';
            $currency = $machine ? $machine->bill_currency : 'TWD';

            try {
                $recordData = [
                    'auth_key_id' => $authKey->id,
                    'token' => $item['token'] ?? null,
                    'machine_type' => $machineType,
                    'credit_in' => $item['credit_in'] ?? 0,
                    'return_value' => $item['return_value'] ?? 0,
                    'timestamp' => Carbon::now(),
                ];
                $record = MachineDataRecord::create($recordData);

                if ($record && $record->exists) {
                    if ($machineType === 'pinball') {
                        MachineDataRecord::create([
                            'record_id' => $record->id,
                            'data_type' => 'ball_in',
                            'value' => $item['ball_in'] ?? 0,
                        ]);
                        MachineDataRecord::create([
                            'record_id' => $record->id,
                            'data_type' => 'ball_out',
                            'value' => $item['ball_out'] ?? 0,
                        ]);
                    } elseif ($machineType === 'gambling') {
                        MachineDataRecord::create([
                            'record_id' => $record->id,
                            'data_type' => 'assign_credit',
                            'value' => $item['assign_credit'] ?? 0,
                        ]);
                        MachineDataRecord::create([
                            'record_id' => $record->id,
                            'data_type' => 'settled_credit',
                            'value' => $item['settled_credit'] ?? 0,
                        ]);
                    } elseif ($machineType === 'bill' && ($item['bill_denomination'] ?? 0) > 0) {
                        $billCount = $item['bill_denomination'] ?? 0;
                        $denomination = $billMappings[$currency][$billCount] ?? 0;
                        if (in_array($denomination, $machine->accepted_denominations ?? [])) {
                            BillRecord::create([
                                'record_id' => $record->id,
                                'bill_denomination' => $billCount,
                                'bill_count' => 1,
                                'timestamp' => Carbon::now(),
                            ]);
                        }
                    }
                    $recordsWritten++;
                }
            } catch (\Exception $e) {
                Log::error("LARAVEL 寫入 machine_data_records 失敗", [
                    'auth_key_id' => $authKey->id ?? null,
                    'error' => $e->getMessage(),
                    'item' => $item
                ]);
            }

            $processedData[] = [
                'machine_name' => $machineName,
                'chip_hardware_id' => $chipHardwareId,
                'auth_key' => $authKeyValue,
                'ball_in' => $item['ball_in'] ?? 0,
                'credit_in' => $item['credit_in'] ?? 0,
                'ball_out' => $item['ball_out'] ?? 0,
                'return_value' => $item['return_value'] ?? 0,
                'assign_credit' => $item['assign_credit'] ?? 0,
                'settled_credit' => $item['settled_credit'] ?? 0,
                'bill_denomination' => $item['bill_denomination'] ?? 0,
            ];
        }

        Log::info("LARAVEL 處理完成，寫入 {$recordsWritten} 筆記錄");
        if (!empty($processedData)) {
            event(new TcpLiveEvent($processedData));
        } else {
            Log::warning("LARAVEL 無有效資料推送至前端");
        }

        exit;
    }

    public function streamIncomingData(Request $request)
    {
        if ($request->header('X-AUTH-TOKEN') !== config('syswaw.tcp_api_key')) {
            Log::info("LARAVEL X-AUTH-TOKEN 未授權 (streamIncomingData)");
            return response()->json(['message' => '未授權'], 403);
        }

        $rawData = $request->json()->all();
        if (empty($rawData)) {
            Log::error("LARAVEL 接收到空資料 (streamIncomingData)");
            return response()->json(['message' => '無效資料'], 400);
        }

        $rawData = is_array($rawData) && !isset($rawData['chip_hardware_id']) ? $rawData : [$rawData];

        response()->json([
            'message' => '即時數據已接收',
            'status' => 'success',
            'data_count' => count($rawData)
        ])->send();

        Log::info('LARAVEL 接收到的即時數據', [
            'X-AUTH-TOKEN' => $request->header('X-AUTH-TOKEN'),
            'payload' => $rawData
        ]);

        $processedData = $this->processData($rawData);

        try {
            if (!empty($processedData)) {
                event(new TcpLiveEvent($processedData));
                Log::info('LARAVEL 已觸發 TcpLiveEvent 事件');
            }
        } catch (\Exception $e) {
            Log::error('LARAVEL 觸發 TcpLiveEvent 事件失敗', [
                'error' => $e->getMessage(),
                'data' => $rawData
            ]);
        }
        exit;
    }
}
