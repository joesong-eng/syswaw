<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log; // 引入 Log facade
use App\Http\Controllers\Tcp\TcpServerController;
use App\Events\TcpScheduleState; // <--- 引入你的事件
use Illuminate\Http\Request; // <--- 引入 Request 類

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ...
        $schedule->command('tcp:schedule')
            ->hourly()
            ->name('tcp_schedule')
            ->withoutOverlapping();

        // 每月1號的 00:10 執行月結報告
        $schedule->command('reports:generate-monthly')->monthlyOn(1, '00:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        // ...
        // 如果你的 GenerateMonthlyReports.php 中 $signature 是 'reports:generate-monthly'
        require base_path('routes/console.php');
    }
}
