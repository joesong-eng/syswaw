<?php
// app/Http/Controllers/Tcp/TcpServerController.php
namespace App\Http\Controllers\Tcp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\MachineData;
use App\Models\MachineAuthKey;
use App\Models\Machine;
use App\Models\Arcade;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse; // 確保有引入
use Illuminate\Support\Carbon; // 確保有引入



class TcpServerController extends Controller
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

    public function control(Request $request): JsonResponse
    {
        $action = $request->input('action');

        // 驗證 action
        if (!in_array($action, ['start', 'stop', 'restart', 'status'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => '無效的指令: ' . $action,
            ], 400);
        }

        try {
            Redis::publish('tcp_server_cmd', json_encode(['action' => $action]));
            Log::info("已發送 TCP 控制命令到 Redis: {$action}"); // 簡化日誌
            return new JsonResponse([
                'status' => 'success',
                'message' => '已發送指令',
                'action' => $action
            ], 200);
        } catch (\Exception $e) {
            Log::error('發佈 TCP Server 控制指令到 Redis 失敗', [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse([
                'status' => 'error',
                'message' => '發送指令失敗: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function openDataGate(Request $request)
    {
        try {
            $records = Redis::command('XREVRANGE', ['tcpstream', '+', '-', 100]);
            $processed = 0;

            foreach ($records as $recordId => $record) {
                if (!isset($record['data']) || !is_string($record['data'])) {
                    Log::warning('Invalid tcpstream data format or missing data field', ['record_id' => $recordId, 'record' => $record]);
                    continue;
                }

                $data = json_decode($record['data'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                    Log::warning('Failed to decode JSON from tcpstream record', ['record_id' => $recordId, 'json_error' => json_last_error_msg(), 'data_string' => $record['data']]);
                    continue;
                }

                if (!isset($data['chip_hardware_id'], $data['auth_key'])) {
                    Log::warning('Missing required fields (chip_hardware_id or auth_key) in tcpstream data', ['record_id' => $recordId, 'data' => $data]);
                    continue;
                }

                $authKey = MachineAuthKey::where('chip_hardware_id', $data['chip_hardware_id'])
                    ->where('auth_key', $data['auth_key'])
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->first();

                if (!$authKey) {
                    Log::warning('Invalid or inactive chip_hardware_id/auth_key combination', ['chip_hardware_id' => $data['chip_hardware_id'], 'auth_key' => $data['auth_key']]);
                    continue;
                }

                $machine = Machine::where('id', $authKey->machine_id)
                    ->where('is_active', true)
                    ->first();

                if (!$machine) {
                    Log::warning('Machine not found or inactive for auth_key', ['machine_id' => $authKey->machine_id, 'auth_key_id' => $authKey->id]);
                    continue;
                }

                $timestamp = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now();

                // 檢查是否已存在相同授權碼和時間戳的記錄，避免重複寫入
                if (MachineData::where('auth_key_id', $authKey->id)
                    ->where('timestamp', $timestamp)
                    ->exists()
                ) {
                    Log::info('Skipping duplicate record based on auth_key_id and timestamp', ['auth_key_id' => $authKey->id, 'timestamp' => $timestamp->toDateTimeString()]);
                    continue;
                }

                try {
                    MachineData::create([
                        'machine_id' => $machine->id,
                        'arcade_id' => $machine->arcade_id,
                        'auth_key_id' => $authKey->id,
                        'machine_type' => $machine->machine_type ?? ($data['machine_type'] ?? 'unknown'), // 優先使用機台本身的類型
                        // Example validation rule in Laravel
                        // 'machine_type' => 'required|string|in:pinball,lottery,bill', // <--- This is what you need to find!
                        'credit_in' => (int)($data['credit_in'] ?? 0),
                        'ball_in' => (int)($data['ball_in'] ?? 0),
                        'ball_out' => (int)($data['ball_out'] ?? 0),
                        'coin_out' => (int)($data['return_value'] ?? 0),
                        'assign_credit' => (int)($data['assign_credit'] ?? 0),
                        'settled_credit' => (int)($data['settled_credit'] ?? 0),
                        'bill_denomination' => (int)($data['bill_denomination'] ?? 0),
                        'error_code' => $data['error_code'] ?? null,
                        'timestamp' => $timestamp,
                    ]);
                    $processed++;
                    Log::info('Successfully processed record from tcpstream', ['record_id' => $recordId, 'machine_id' => $machine->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to create MachineData record', [
                        'error' => $e->getMessage(),
                        'record_id' => $recordId,
                        'data_payload' => $data
                    ]);
                    continue;
                }
            }

            Log::info('openDataGate execution complete', ['records_processed' => $processed]);
            return response()->json([
                'status' => 'success',
                'message' => "已處理 {$processed} 筆記錄"
            ], 200);
        } catch (\Exception $e) {
            Log::error('openDataGate failed due to unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => '處理資料失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    public function streamData()
    {
        // 獲取 TCP Server 狀態
        $status = app('redis')->get('tcp_status') ?? 'stopped';

        return view('admin.tcp.streamData', compact(
            'status',
        ));
    }


    public function query()
    {
        // 確保 Validator 有被引入：use Illuminate\Support\Facades\Validator;
        $machineIds = request()->input('machine_ids');
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [ // 使用完整命名空間
            'machine_ids' => 'required|array',
            'machine_ids.*' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '無效的機器 ID 格式',
                'errors' => $validator->errors(),
            ], 400);
        }

        $message = json_encode(['type' => 'query', 'machine_ids' => $machineIds]);
        try {
            Redis::rpush('tcp_control_list', $message); // 使用列表作為隊列
            Log::info('已將查詢指令添加到 Redis 列表', ['message' => $message]);
            return response()->json(['message' => '查詢指令已發送']);
        } catch (\Exception $e) {
            Log::error('將查詢指令添加到 Redis 列表失敗: ' . $e->getMessage(), ['message' => $message, 'error' => $e]);
            return response()->json([
                'message' => '發送查詢指令失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
