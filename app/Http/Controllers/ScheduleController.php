<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function controlSchedule(Request $request)
    {
        $action = $request->input('action');
        $interval = $request->input('interval');

        if (!in_array($action, ['start_schedule', 'stop_schedule'])) {
            Log::error('Invalid schedule action', ['action' => $action]);
            return response()->json(['error' => '無效的定時操作'], 400);
        }

        try {
            $status = '';
            $broadcastInterval = null;

            if ($action === 'start_schedule') {
                if (!$interval || !in_array($interval, [1, 2, 6, 12, 24])) {
                    Log::error('Invalid schedule interval', ['interval' => $interval]);
                    return response()->json(['error' => '無效的定時間隔'], 400);
                }
                $status = 'running';
                $broadcastInterval = (int) $interval;

                Log::info('Schedule started', ['interval' => $interval]);
            } elseif ($action === 'stop_schedule') {
                $status = 'stopped';
                $broadcastInterval = null;

                Log::info('Schedule stopped');
            }

            return response()->json(['message' => "已發送 '{$action}' 命令，狀態已廣播"]);
        } catch (\Exception $e) {
            Log::error('Failed to send schedule control command', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => "發送 '{$action}' 命令失敗: {$e->getMessage()}"], 500);
        }
    }
}
