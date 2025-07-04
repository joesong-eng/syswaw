<?php

/**負責處理前端人為操作（如啟動/停止 TCP Server） */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Events\ReverbServerStatusUpdated;

class EtherealController extends Controller
{
    public function index()
    {
        $status = Redis::get('tcp_status') ?? 'stopped';
        return view('ethereal/index', compact('status'));
    }
    public function xbroadcast(Request $request)
    {
        $action = $request->json('action');
        broadcast(new ReverbServerStatusUpdated($action)); // Reverb 廣播
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Broadcasted via event.',
                'action' => $action
            ]);
        }
        return redirect()->route('ethereal.index')->with('success', 'c.');
    }
    public function xevent(Request $request)
    {
        $action = $request->input('action');
        event(new ReverbServerStatusUpdated($action)); // Reverb 廣播
        // 檢查是否是 AJAX / JSON 請求
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Broadcasted via event.',
                'action' => $action
            ]);
        }
        return redirect()->route('ethereal.index')->with('success', 'v.');
    }
    public function redis_ctrl(Request $request)
    {
        $action = $request->input('action');
        Redis::publish('tcp_server_control', $action); // Redis 廣播
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Broadcasted via event.',
                'action' => $action
            ]);
        }
        return redirect()->route('ethereal.index')->with('success', 'b.');
    }
}
