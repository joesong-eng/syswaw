<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Tcp\Api\TcpStatusController;
use Illuminate\Support\Facades\Log; // 引入 Log Facade

class SubscribeToRedis extends Command
{
    protected $signature = 'redis:subscribe';
    protected $description = 'Subscribe to Redis channels for Python TCP server responses';

    public function handle()
    {
        // 委派訂閱邏輯給 SubscribeToRedis.php 的 subscribeToRedis 方法。
        $msg = 'LARAVEL *0* [SubscribeToRedis@handle]TcpStatusController.php 的 subscribeToRedis 方法';
        Log::channel('redis_cmd')->info($msg);

        $controller = new TcpStatusController();
        $controller->subscribeToRedis();
    }
}
