<?php
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use App\Events\ReverbServerStatusUpdated;
use App\Http\Controllers\TcpStatusController;
// Route::middleware('api')->group(function () {
//     // 你的API路由
// });

Route::get('/tcp-status', [TcpStatusController::class, 'update']);
Route::post('/tcp-status', [TcpStatusController::class, 'update']);

