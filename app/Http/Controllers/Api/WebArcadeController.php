<?php
// app/Http/Controllers/Api/WebArcadeController.php
/**專門處理從 Python 回傳的狀態更新 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Events\TcpServerState;
use App\Events\ReverbPush;
use App\Models\ChipKey;
use App\Models\Machine;

class WebArcadeController extends Controller
{
    public function arcade(Request $request)
    {
        // if ($request->header('X-AUTH-TOKEN') !== config('syswaw.tcp_api_key')) {
        //     \Log::info("X-AUTH-TOKEN 未授權");
        //     return response()->json(['message' => '未授權'], 403);
        // }
        $status = $request->input('status');
        $action = $request->input('action');
        response()->json([ // ✅ 先回應給 Python timeout=5 超過python會記錄錯誤 logging.error
            'message' => 'broadcasted',
            'status' => $status,
            'action' => $action
        ])->send();
        \Log::info("status {$status} $status");
        \Log::info('接收到的 TCP 請求', [
            'X-AUTH-TOKEN' => $request->header('X-AUTH-TOKEN'),
            'payload' => $request->all(),
            'TCP_API_KEY_ENV' => config('syswaw.tcp_api_key')
        ]);
        exit;
    }
}
