<?php
/**專門處理從 Python 回傳的狀態更新 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Events\TcpServerState;
use App\Events\ReverbServerStatusUpdated;
use Illuminate\Support\Facades\Log; // 引入 Log facade

class TcpStatusController extends Controller{
    public function update(Request $request){
        \Log::info('接收到的 TCP 請求', [
            'X-AUTH-TOKEN' => $request->header('X-AUTH-TOKEN'),
            'payload' => $request->all(),
            'TCP_API_KEY_ENV' =>config('syswaw.tcp_api_key')
        ]);
        if ($request->header('X-AUTH-TOKEN') !== config('syswaw.tcp_api_key')) {
            \Log::info("X-AUTH-TOKEN 未授權");
            return response()->json(['message' => '未授權'], 403);
        }
        $status = $request->input('status');
        $action = $request->input('action');
        \Log::info("status {$status} $status");
        event(new TcpServerState($status, $action)); // 同時傳遞 status 和 action
        
        return response()->json(['message' => '狀態已廣播', 'status' => $status, 'action' => $action]); // 在響應中也包含 action
    }
}

