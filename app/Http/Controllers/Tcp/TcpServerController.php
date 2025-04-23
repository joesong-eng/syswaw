<?php

namespace App\Http\Controllers\Tcp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Events\ReverbServerStatusUpdated;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use App\Models\MachineDataRecord; // 引入 MachineDataRecord Model
use App\Models\Machine;           // 引入 Machine Model
use App\Models\Arcade;             // 引入 Arcade Model
use Illuminate\Support\Facades\Log;

class TcpServerController extends Controller
{
    /**
     * 顯示 TCP Server 狀態及歷史數據記錄列表。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 獲取 TCP Server 狀態
        $status = app('redis')->get('tcp_status') ?? 'stopped';

        // ====== 歷史數據獲取與篩選 ======

        // 獲取篩選條件
        $filterArcadeId = $request->input('arcade_id');
        $filterMachineId = $request->input('machine_id'); // 從前端接收的是 machines.id

        // 建立查詢
        $query = MachineDataRecord::query();

        // 預加載關聯，以便後續使用和篩選
        // 需要在 MachineDataRecord Model 中定義 machine 關聯，以及在 Machine Model 中定義 arcade 關聯
        $query->with(['machine.arcade']);

        // 應用 Arcade 篩選
        if ($filterArcadeId && $filterArcadeId !== 'all') {
            $query->whereHas('machine.arcade', function ($q) use ($filterArcadeId) {
                $q->where('arcades.id', $filterArcadeId);
            });
        }

        // 應用 Machine 篩選 (根據 machines.id 過濾)
        if ($filterMachineId && $filterMachineId !== 'all') {
            // 找到對應的 Chip ID (因為 MachineDataRecord 是用 chip_id 關聯 Machine)
            // 這裡直接使用 whereHas 透過 machine 關聯來過濾更方便
            $query->whereHas('machine', function ($q) use ($filterMachineId) {
                $q->where('machines.id', $filterMachineId);
            });
        }

        // 排序：按時間戳倒序排列
        $query->orderBy('timestamp', 'desc'); // 假設 timestamp 是你希望排序的欄位

        // 分頁：每頁 30 條
        $records = $query->paginate(30);

        // 獲取所有 Arcade 和 Machine 以填充篩選下拉框
        $arcades = Arcade::all();
        // 獲取所有 Machines，以便在 Machine 篩選下拉框中使用
        // 這裡只獲取 ID 和 name 欄位減少數據量
        $machines = Machine::select('id', 'name', 'arcade_id')->get();


        // 將數據傳遞給 View
        return view('admin.tcp.index', compact(
            'status',
            'records',
            'arcades',
            'machines',
            'filterArcadeId', // 傳遞當前篩選值回 View 以保留選中狀態
            'filterMachineId'
        ));
    }

    /**
     * 接收前端請求，發佈機器 ID 到 Redis Channel 觸發數據抓取。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function openDataGate(Request $request)
    {
        // 1. 驗證接收到的數據
        $validator = Validator::make($request->all(), [
            'machine_ids' => 'required|array', // 確保 machine_ids 存在且是一個陣列
            'machine_ids.*' => 'integer|min:1', // 確保陣列中的每個元素都是大於等於 1 的整數
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '無效的機器 ID 格式',
                'errors' => $validator->errors(),
            ], 400); // 400 Bad Request
        }

        $machineIds = $request->input('machine_ids');

        // ====== 新增：根據 Machine ID 查找對應的 Chip ID ======
        $chipKeysToPublish = Machine::whereIn('id', $machineIds)
            ->pluck('chip_id') // 獲取對應的 chip_id 列表
            ->filter(); // 移除可能為 null 的 chip_id

        if ($chipKeysToPublish->isEmpty()) {
            return response()->json([
                'message' => '找不到對應的機器晶片 ID。',
            ], 404); // 404 Not Found 或 422 Unprocessable Entity
        }


        // 2. 將 Chip ID 陣列轉換為 JSON 字串，因為 Redis Publish 需要字串
        // 發佈的是 Chip ID 列表，而不是 Machine ID 列表
        $message = json_encode($chipKeysToPublish->values()->all()); // values().all() 確保是從 0 開始的索引陣列

        // 3. 發佈到 Redis Channel
        // 檢查 Redis 連線是否正常，避免應用崩潰
        try {
            Redis::publish('open_gate_channel', $message);
        } catch (\Exception $e) {
            // 記錄錯誤或回傳錯誤給前端
            Log::error('Failed to publish Chip IDs to Redis channel open_gate_channel: ' . $e->getMessage());
            return response()->json([
                'message' => 'Redis 發佈失敗，無法觸發數據抓取。',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }


        // 4. 回傳成功響應給前端
        return response()->json(['message' => '抓取晶片指令已發送，請稍候。']);
    }

    public function control(Request $request)
    {
        $action = $request->input('action');
        Redis::publish('tcp_server_control', $action); // Redis 廣播
        return redirect('/tcp-server');
    }

    public function query()
    {
        $machineIds = request()->input('machine_ids');
        Redis::rpush('tcp_control', 'query:' . implode(',', $machineIds));
        return response()->json(['message' => 'Machine IDs 已更新']);
    }

    public function stream()
    {
        return response()->stream(function () {
            Redis::subscribe(['tcp_status_channel'], function ($message) {
                echo "data: " . json_encode($message) . "\n\n";
                ob_flush();
                flush();
            });
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }
}

// public function start(){
//     Redis::rpush('tcp_control', 'start');
//     return response()->json(['message' => 'start']);
// }

// public function stop(){
//     Redis::rpush('tcp_control', 'stop');
//     return response()->json(['message' => 'stop']);
// }

// public function restart()
// {
//     Redis::rpush('tcp_control', 'restart');
//     return response()->json(['message' => 'restart']);
// }