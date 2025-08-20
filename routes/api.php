<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachineStatusController; // 引入狀態控制器

use Illuminate\Http\Request;
use App\Http\Controllers\Tcp\Api\TcpStatusController;
use App\Http\Controllers\Api\WebArcadeController;
use App\Http\Controllers\Tcp\TcpServerController;
// use App\Http\Controllers\MachineDataController;
// Route::middleware('api')->group(function () {
//     // 你的API路由
// });


// Route::post('/machine-data', [MachineDataController::class, 'store']);

Route::get('/tcp-status', [TcpStatusController::class, 'update']);
Route::post('/tcp-status', [TcpStatusController::class, 'update']);
Route::get('/tcp-schedule-status', [TcpStatusController::class, 'updateScheduleStatus']); // **** 新增：定時狀態回報 API ****
Route::post('/tcp-schedule-status', [TcpStatusController::class, 'updateScheduleStatus']); // **** 新增：定時狀態回報 API ****

Route::get('/tcp-data-captured', [TcpStatusController::class, 'dataCaptured']);
Route::post('/tcp-data-captured', [TcpStatusController::class, 'dataCaptured']);

// **** 新增：用於接收 Python 的即時數據並廣播 ****
Route::get('/stream-incoming-data', [TcpStatusController::class, 'streamIncomingData']);
Route::post('/stream-incoming-data', [TcpStatusController::class, 'streamIncomingData']);


Route::get('/web-arcade', [WebArcadeController::class, 'arcade']);
Route::post('/web-arcade', [WebArcadeController::class, 'arcade']);

// 這是給您的 MQTT Subscriber 使用的專用 API 端點
Route::post('/machine-data', function (Request $request) {
    // 記錄接收到的原始數據
    Log::info('API: /machine-data received raw data', ['data' => $request->all()]);

    // 簡單的驗證，確保 chip_id 存在
    $validated = $request->validate([
        'chip_id' => 'required|string',
        '*' => '', // 允許其他任何欄位
    ]);

    // 記錄驗證後的數據
    Log::info('API: /machine-data validated data', ['validated_data' => $validated]);

    event(new \App\Events\MachineDataReceived($validated));
    return response()->json(['status' => 'success', 'data' => $validated]);
});

// 新增路由用於接收 Python 的狀態更新並廣播
Route::post('/machine-status', [MachineStatusController::class, 'updateStatus']);
