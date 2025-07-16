<?php
// routes/api.php
use Illuminate\Support\Facades\Route;

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
