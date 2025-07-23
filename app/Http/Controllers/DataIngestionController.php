<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\MachineData;
use App\Models\MachineAuthKey;
use App\Models\Machine;
use App\Models\Arcade;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DataIngestionController extends Controller
{
    public function index(Request $request)
    {
        // 1. 獲取篩選參數並設定預設值
        // 使用 input() 輔助函數，第二個參數是預設值
        $filterArcadeId = $request->input('arcade_id', 'all');
        $filterMachineId = $request->input('machine_id', 'all');
        // 將預設時間篩選改為 'all'，表示不應用時間篩選
        $filterTimeRange = $request->input('time_filter', 'all');

        // 2. 初始化 MachineData 查詢建構器，並預載相關模型
        $query = MachineData::with(['arcade', 'machine']);

        // 3. 應用篩選條件 (正確處理 'all' 選項)
        if ($filterArcadeId !== 'all') {
            $query->where('arcade_id', $filterArcadeId);
        }

        if ($filterMachineId !== 'all') {
            $query->where('machine_id', $filterMachineId);
        }

        // 4. 應用時間篩選
        $now = Carbon::now(); // 使用 Carbon 處理時間
        // 增量統計和趨勢圖表會用到這些，先初始化
        $timeFilterStartForStats = null;
        $timeFilterEndForStats = null;

        switch ($filterTimeRange) {
            case 'today':
                $query->whereDate('timestamp', $now->toDateString());
                $timeFilterStartForStats = $now->copy()->startOfDay();
                $timeFilterEndForStats = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $query->whereDate('timestamp', $now->copy()->subDay()->toDateString());
                $timeFilterStartForStats = $now->copy()->subDay()->startOfDay();
                $timeFilterEndForStats = $now->copy()->subDay()->endOfDay();
                break;
            case 'last_3_days':
                // 注意：這裡應該是包含今天在內的過去三天
                $query->where('timestamp', '>=', $now->copy()->subDays(2)->startOfDay());
                $query->where('timestamp', '<=', $now->copy()->endOfDay());
                $timeFilterStartForStats = $now->copy()->subDays(2)->startOfDay();
                $timeFilterEndForStats = $now->copy()->endOfDay();
                break;
            case 'last_7_days':
                // 包含今天在內的過去七天
                $query->where('timestamp', '>=', $now->copy()->subDays(6)->startOfDay());
                $query->where('timestamp', '<=', $now->copy()->endOfDay());
                $timeFilterStartForStats = $now->copy()->subDays(6)->startOfDay();
                $timeFilterEndForStats = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $query->whereBetween('timestamp', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                $timeFilterStartForStats = $now->copy()->startOfWeek();
                $timeFilterEndForStats = $now->copy()->endOfWeek();
                break;
            case 'last_week':
                $query->whereBetween('timestamp', [
                    $now->copy()->subWeek()->startOfWeek(),
                    $now->copy()->subWeek()->endOfWeek()
                ]);
                $timeFilterStartForStats = $now->copy()->subWeek()->startOfWeek();
                $timeFilterEndForStats = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $query->whereBetween('timestamp', [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ]);
                $timeFilterStartForStats = $now->copy()->startOfMonth();
                $timeFilterEndForStats = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $query->whereBetween('timestamp', [
                    $now->copy()->subMonthNoOverflow()->startOfMonth(),
                    $now->copy()->subMonthNoOverflow()->endOfMonth()
                ]);
                $timeFilterStartForStats = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $timeFilterEndForStats = $now->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'all':
            default:
                // 當選擇 'all' 或沒有提供 time_filter 時，不應用時間篩選
                // 這裡可以選擇將時間範圍設定為一個非常大的範圍，或者不設定
                // 對於統計和趨勢圖，如果 time_filter 是 'all'，則 $timeFilterStartForStats 和 $timeFilterEndForStats 應該為 null，表示不限制時間
                $timeFilterStartForStats = null;
                $timeFilterEndForStats = null;
                break;
        }

        // 5. 排序和分頁 (只執行一次)
        $records = $query->orderBy('timestamp', 'desc')->paginate(30);

        // 6. 增量統計 (確保篩選條件與上方數據列表一致，並使用正確的時間範圍)
        $statsQuery = MachineData::query();
        if ($filterArcadeId !== 'all') {
            $statsQuery->where('arcade_id', $filterArcadeId);
        }
        if ($filterMachineId !== 'all') {
            $statsQuery->where('machine_id', $filterMachineId);
        }
        if ($timeFilterStartForStats && $timeFilterEndForStats) {
            $statsQuery->whereBetween('timestamp', [$timeFilterStartForStats, $timeFilterEndForStats]);
        }
        $stats = $statsQuery->orderBy('timestamp')->select('credit_in', 'ball_in')->get();

        $credit_in_increment = $stats->isNotEmpty() ? ($stats->last()->credit_in - $stats->first()->credit_in) : 0;
        $ball_in_increment = $stats->isNotEmpty() ? ($stats->last()->ball_in - $stats->first()->ball_in) : 0;

        // 7. 趨勢圖表 (同樣確保篩選條件一致)
        $trendsQuery = MachineData::query();
        if ($filterArcadeId !== 'all') {
            $trendsQuery->where('arcade_id', $filterArcadeId);
        }
        if ($filterMachineId !== 'all') {
            $trendsQuery->where('machine_id', $filterMachineId);
        }
        if ($timeFilterStartForStats && $timeFilterEndForStats) {
            $trendsQuery->whereBetween('timestamp', [$timeFilterStartForStats, $timeFilterEndForStats]);
        }

        $trends = $trendsQuery->groupBy(DB::raw('DATE(timestamp)'))
            ->selectRaw('DATE(timestamp) as date, MAX(credit_in) - MIN(credit_in) as credit_in_increment, MAX(ball_in) - MIN(ball_in) as ball_in_increment')
            ->get();

        $chartData = [
            'labels' => $trends->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Credit In Increment',
                    'data' => $trends->pluck('credit_in_increment'),
                    'borderColor' => '#4CAF50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.2)',
                    'fill' => true
                ],
                [
                    'label' => 'Ball In Increment',
                    'data' => $trends->pluck('ball_in_increment'),
                    'borderColor' => '#2196F3',
                    'backgroundColor' => 'rgba(33, 150, 243, 0.2)',
                    'fill' => true
                ]
            ]
        ];

        // 8. 獲取所有街機和機器資料 (這些通常不需要篩選，用於下拉選單)
        $arcades = Arcade::all();
        $machines = Machine::all();

        // 9. 從 Redis 獲取實時的 TCP Server 狀態
        $status = app('redis')->get('tcp_status') ?? 'stopped';
        // 10. 將所有資料傳遞到視圖
        return view('admin.tcp.index', compact(
            'records',
            'arcades',
            'machines',
            'filterArcadeId',
            'filterMachineId',
            'filterTimeRange',
            'status',
            'chartData', // 如果要在頁面上顯示趨勢圖，需要傳遞
            'credit_in_increment', // 傳遞增量統計結果
            'ball_in_increment' // 傳遞增量統計結果
        ));
    }
    /**
     * 應該是棄用了,待確認後刪除
     * 主方法：處理前端或定時任務的數據擷取請求。
     *
     * @param Request $request
     * @return JsonResponse
     */
    // public function ingestMqttData(Request $request): JsonResponse
    // {
    //     $lockKey = 'data_ingestion_lock';
    //     $lockTimeout = 30; // 設置較長的鎖定時間，以確保處理完成
    //     // 嘗試獲取 Redis 鎖 (使用 SET NX EX 原子操作)
    //     $acquiredLock = Redis::set($lockKey, 'locked', 'EX', $lockTimeout, 'NX');

    //     if (!$acquiredLock) {
    //         Log::warning('DataIngestionController: 請求被鎖定，避免重複執行。', ['lockKey' => $lockKey]);
    //         return response()->json(['status' => 'warning', 'message' => '數據擷取正在處理中，請勿重複提交。'], 429);
    //     }

    //     try {
    //         $processedRecords = $this->processRedisStreamData();
    //         // 處理完成後釋放鎖
    //         Redis::del($lockKey);
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => '數據擷取與寫入完成',
    //             'processed_count' => count($processedRecords),
    //             'details' => $processedRecords // 可以返回處理詳情
    //         ]);
    //     } catch (\Exception $e) {
    //         // 發生錯誤時也釋放鎖
    //         Redis::del($lockKey);
    //         Log::error('DataIngestionController: 數據擷取與寫入失敗', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => '數據擷取與寫入失敗: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * 從 Redis Key-Value 儲存獲取最新的 MQTT 數據，並寫入資料庫。
     *
     * @return array 處理後的記錄詳情
     */
    public function processRedisStreamData(): array
    {
        // 獲取所有以 'machine_data:' 開頭的 Key
        $keys = Redis::keys('machine_data:*');

        if (empty($keys)) {
            return [];
        }

        $processedData = [];
        foreach ($keys as $key) {
            $jsonData = Redis::get($key); // 從 Redis 獲取數據

            if (empty($jsonData)) {
                Log::warning('DataIngestionController: 從 Redis Key 獲取到空數據，跳過。', ['key' => $key]);
                // 考慮在這裡刪除空 Key，如果確定是無效數據
                // Redis::del($key);
                continue;
            }

            $decodedData = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('DataIngestionController: 無法從 Redis Key 解碼 JSON 數據.', ['key' => $key, 'jsonData' => $jsonData, 'jsonError' => json_last_error_msg()]);
                $processedData[] = [
                    'key' => $key,
                    'status' => 'json_decode_failed',
                    'error_message' => json_last_error_msg()
                ];
                continue;
            }

            $timestampFromPayload = $decodedData['timestamp'] ?? null;
            $isoTimestamp = null;

            if ($timestampFromPayload) {
                try {
                    $isoTimestamp = Carbon::parse($timestampFromPayload)->toIso8601String();
                } catch (\Exception $e) {
                    Log::warning('DataIngestionController: 無法解析數據 payload 中的時間戳，跳過記錄。', [
                        'key' => $key,
                        'timestamp' => $timestampFromPayload,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            } else {
                Log::warning('DataIngestionController: 解碼數據 payload 中缺少時間戳字段，跳過記錄。', ['key' => $key, 'decodedData' => $decodedData]);
                continue;
            }

            // 獲取機台和認證金鑰信息
            $machineId = null;
            $arcadeId = null;
            $authKeyId = null;
            $machineType = null;
            $machineName = 'N/A';
            $authKeyString = 'N/A';

            $authKeyFromPayload = $decodedData['auth_key'] ?? null; // 從 payload 中獲取 auth_key

            if ($authKeyFromPayload) {
                $authKeyModel = MachineAuthKey::where('auth_key', $authKeyFromPayload) // 使用 auth_key 查詢
                    ->where('status', 'active')
                    ->latest('created_at')
                    ->first();

                if ($authKeyModel) {
                    $machine = Machine::where('id', $authKeyModel->machine_id)
                        ->where('is_active', true)
                        ->first();

                    if ($machine) {
                        $machineId = $machine->id;
                        $arcadeId = $machine->arcade_id;
                        $authKeyId = $authKeyModel->id;
                        $machineType = $machine->machine_type;
                        $machineName = $machine->name;
                        $authKeyString = $authKeyModel->auth_key;
                    } else {
                        Log::warning('DataIngestionController: 機台未找到或不活躍 for auth_key.', ['auth_key' => $authKeyFromPayload, 'machine_id' => $authKeyModel->machine_id]);
                    }
                } else {
                    Log::warning('DataIngestionController: 未找到活躍的 auth_key.', ['auth_key' => $authKeyFromPayload]);
                }
            } else {
                Log::warning('DataIngestionController: payload 中缺少 auth_key，無法進行機台查找.', ['key' => $key, 'decodedData' => $decodedData]);
            }

            // 額外檢查：基於 auth_key_id 和 timestamp 的重複判斷
            // 由於 Redis 中每個 Key 已經是最新數據，這裡的重複判斷主要用於防止數據庫層面的重複寫入
            if (
                $authKeyId && MachineData::where('auth_key_id', $authKeyId)
                ->where('timestamp', $isoTimestamp)
                ->exists()
            ) {
                Log::info('DataIngestionController: 基於 auth_key_id 和 timestamp 跳過重複記錄', ['auth_key_id' => $authKeyId, 'timestamp' => $isoTimestamp]);
                $processedData[] = [
                    'key' => $key,
                    'status' => 'skipped_duplicate_by_timestamp',
                    'message' => '重複記錄 (時間戳)'
                ];
                // 處理完畢後，從 Redis 中刪除該 Key，避免下次重複處理
                // Redis::del($key); // 這裡不刪除，因為我們希望 Redis 保持最新數據
                continue;
            }

            // 只有當找到有效的 machine_id, arcade_id, auth_key_id 時才嘗試寫入
            if ($machineId && $arcadeId && $authKeyId) {
                try {
                    MachineData::create([
                        'machine_id' => $machineId,
                        'arcade_id' => $arcadeId,
                        'auth_key_id' => $authKeyId,
                        'machine_type' => $machineType ?? ($decodedData['machine_type'] ?? 'unknown'),
                        'credit_in' => (int)($decodedData['credit_in'] ?? 0),
                        'ball_in' => (int)($decodedData['ball_in'] ?? 0),
                        'ball_out' => (int)($decodedData['ball_out'] ?? 0),
                        'coin_out' => (int)($decodedData['coin_out'] ?? 0),
                        'assign_credit' => (int)($decodedData['assign_credit'] ?? 0),
                        'settled_credit' => (int)($decodedData['settled_credit'] ?? 0),
                        'bill_denomination' => (int)($decodedData['bill_denomination'] ?? 0),
                        'error_code' => $decodedData['error_code'] ?? null,
                        'timestamp' => $isoTimestamp,
                        // 'redis_stream_id' => $recordId, // 不再儲存 Redis Stream ID
                    ]);
                    $processedData[] = [
                        'key' => $key,
                        'status' => 'saved_to_db',
                        'machine_name' => $machineName,
                        'auth_key' => $authKeyFromPayload,
                        'timestamp' => $isoTimestamp
                    ];
                    Log::info('DataIngestionController: 成功處理並儲存記錄', ['key' => $key, 'machine_id' => $machineId]);
                    // 處理完畢後，從 Redis 中刪除該 Key
                    Redis::del($key);
                } catch (\Exception $e) {
                    Log::error('DataIngestionController: 無法創建 MachineData 記錄', [
                        'error' => $e->getMessage(),
                        'key' => $key,
                        'data_payload' => $decodedData
                    ]);
                    $processedData[] = [
                        'key' => $key,
                        'status' => 'failed_to_save',
                        'error_message' => $e->getMessage()
                    ];
                }
            } else {
                Log::warning('DataIngestionController: 缺少必要的機台信息，無法儲存記錄。', ['key' => $key, 'authKeyFromPayload' => $authKeyFromPayload, 'decodedData' => $decodedData]);
                $processedData[] = [
                    'key' => $key,
                    'status' => 'missing_machine_info',
                    'message' => '缺少機台或認證金鑰信息'
                ];
            }
        }

        return $processedData;
    }
    public function streamData()
    {
        $keys = Redis::keys('machine_data:*');
        foreach ($keys as $key) {
            $jsonData = Redis::get($key);
            $decodedData = json_decode($jsonData, true);
            $authKeyFromPayload = $decodedData['auth_key'] ?? null; // 從 payload 中獲取 auth_key
            $authKeyModel = MachineAuthKey::where('auth_key', $authKeyFromPayload)
                ->where('status', 'active')
                ->latest('created_at')
                ->first();
            // dd($authKeyModel);
            if (isset($authKeyModel->machine_id)) {
                $machine = Machine::where('id', $authKeyModel->machine_id)
                    ->where('is_active', true)
                    ->first();
                // dump(isset($machine->name) ? $machine->name : "");
            }
        }
        return view('admin.tcp.streamData');
    }
    /**
     * 從 Redis Key-Value 儲存獲取最新的 MQTT 數據，僅用於顯示，不寫入資料庫。
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStreamMqttData(): JsonResponse
    {
        // return "????????";
        try {
            // 獲取所有以 'machine_data:' 開頭的 Key
            $keys = Redis::keys('machine_data:*');

            if (empty($keys)) {
                return response()->json([]);
            }

            $processedData = [];
            foreach ($keys as $key) {

                $jsonData = Redis::get($key); // 從 Redis 獲取數據
                if (empty($jsonData)) {
                    Log::warning('DataIngestionController: 從 Redis Key 獲取到空數據，跳過 (只讀模式)。', ['key' => $key]);
                    continue;
                }

                $decodedData = json_decode($jsonData, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('DataIngestionController: 無法從 Redis Key 解碼 JSON 數據 (只讀模式).', ['key' => $key, 'jsonData' => $jsonData, 'jsonError' => json_last_error_msg()]);
                    $processedData[] = [
                        'key' => $key,
                        'status' => 'json_decode_failed',
                        'error_message' => json_last_error_msg()
                    ];
                    continue;
                }

                $timestampFromPayload = $decodedData['timestamp'] ?? null;
                $isoTimestamp = null;

                if ($timestampFromPayload) {
                    try {
                        $isoTimestamp = Carbon::parse($timestampFromPayload)->toIso8601String();
                    } catch (\Exception $e) {
                        Log::warning('DataIngestionController: 無法解析數據 payload 中的時間戳，跳過記錄 (只讀模式)。', [
                            'key' => $key,
                            'timestamp' => $timestampFromPayload,
                            'error' => $e->getMessage(),
                        ]);
                        continue;
                    }
                } else {
                    Log::warning('DataIngestionController: 解碼數據 payload 中缺少時間戳字段，跳過記錄 (只讀模式)。', ['key' => $key, 'decodedData' => $decodedData]);
                    continue;
                }

                $machineName = 'N/A';
                $authKeyString = 'N/A';
                $machineId = null;
                $arcadeId = null;
                $machineType = null;

                $authKeyFromPayload = $decodedData['auth_key'] ?? null; // 從 payload 中獲取 auth_key

                if ($authKeyFromPayload) {
                    $authKeyModel = MachineAuthKey::where('auth_key', $authKeyFromPayload)
                        ->where('status', 'active')
                        ->latest('created_at')
                        ->first();

                    if ($authKeyModel) {
                        $machine = Machine::where('id', $authKeyModel->machine_id)
                            ->where('is_active', true)
                            ->first();

                        if ($machine) {
                            $machineName = $machine->name;
                            $machineId = $machine->id;
                            $arcadeId = $machine->arcade_id;
                            $machineType = $machine->machine_type;
                            $authKeyString = $authKeyModel->auth_key;

                            $arcadeName = 'N/A';
                            if ($arcadeId) {
                                $arcade = Arcade::find($arcadeId);
                                if ($arcade) {
                                    $arcadeName = $arcade->name;
                                }
                            }

                            // 只有當找到有效的機台資訊時才加入 processedData
                            $processedData[] = [
                                'data' => $decodedData,
                                'timestamp' => $isoTimestamp,
                                'machine_name' => $machineName,
                                'auth_key_string' => $authKeyString,
                                'machine_id' => $machineId,
                                'arcade_id' => $arcadeId,
                                'arcade_name' => $arcadeName, // 新增店舖名稱
                                'machine_type' => $machineType,
                                'status' => 'read_only' // 標記為只讀數據
                            ];
                        } else {
                            Log::warning('DataIngestionController: 機台未找到或不活躍 for auth_key (只讀模式).', ['auth_key' => $authKeyFromPayload, 'machine_id' => $authKeyModel->machine_id]);
                        }
                    } else {
                        Log::warning('DataIngestionController: 未找到活躍的 auth_key (只讀模式).', ['auth_key' => $authKeyFromPayload]);
                    }
                } else {
                    Log::warning('DataIngestionController: payload 中缺少 auth_key，無法進行機台查找 (只讀模式).', ['key' => $key, 'decodedData' => $decodedData]);
                }
            }

            return response()->json($processedData);
        } catch (\Exception $e) {
            Log::error('DataIngestionController: 無法從 Redis 獲取最新的 MQTT 數據 (只讀模式)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => '無法從 Redis 獲取數據'], 500);
        }
    }
}
