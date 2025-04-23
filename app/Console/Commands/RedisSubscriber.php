<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisSubscriber extends Command
{
    protected $signature = 'redis:subscribe';
    protected $description = '訂閱 Redis 頻道，並處理 tcp_status_channel 的訊息';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('開始訂閱 tcp_status_channel 頻道...');

        Redis::subscribe(['tcp_status_channel'], function ($message) {
            Log::info('收到來自 tcp_status_channel 的訊息：' . json_encode($message));

            // 將訊息儲存到暫時性的 Redis List
            Redis::rpush('sse_messages', json_encode($message));
        });
    }
}