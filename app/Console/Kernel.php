<?php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Events\TcpScheduleState;

// use Illuminate\Support\Facades\Log;
// use App\Http\Controllers\Tcp\TcpServerController;
// use App\Http\Controllers\DataIngestionController;
// use Illuminate\Http\Request;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 任務1: 每天的 0:00 將 Redis 每台遊戲機的數據寫入資料庫
        $schedule->command('data:sync-redis-to-db')
            // ->dailyAt('00:00') // 每天午夜 00:00 執行
            ->everyFiveMinutes() //測試每5分鐘
            ->withoutOverlapping()
        ;

        // 將數據擷取任務改為每小時執行，以便測試 ＊＊應該是棄用了,待確認後刪除
        // $schedule->call(function () {
        //     app(DataIngestionController::class)->ingestMqttData(new Request());
        // }) // ->everyFiveMinutes()
        //     ->hourly()
        //     ->name('ingest_mqtt_data_task') // 添加唯一的任務名稱
        //     ->withoutOverlapping()
        // ;

        // 任務2: 每月要結帳的動作
        $schedule->command('reports:generate-monthly')
            ->monthlyOn(1, '00:10') // 每月1號的 00:10 執行
            ->withoutOverlapping();

        // 每月1號的 00:10 執行月結報告
        // $schedule->command('reports:generate-monthly')->monthlyOn(1, '00:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        // 如果你的 GenerateMonthlyReports.php 中 $signature 是 'reports:generate-monthly'
        require base_path('routes/console.php');
    }

    // protected function schedule(Schedule $schedule)
    // {
    //     $schedule->call(function () {
    //         try {
    //             $controller = app(TcpServerController::class);
    //             $response = $controller->openDataGate(new Request());
    //             Log::info('每分钟任务测试成功', ['response' => $response->getContent()]);
    //             event(new TcpScheduleState('running', "")); // 不需要错误信息
    //         } catch (\Exception $e) {
    //             Log::error('每分钟任务测试失败', [
    //                 'error' => $e->getMessage(),
    //                 'trace' => $e->getTraceAsString()
    //             ]);
    //             event(new TcpScheduleState('error', "", $e->getMessage())); // 传递错误信息
    //         }
    //     })->everyMinute()
    //         ->name('test_per_minute')
    //         ->withoutOverlapping();
    // }
}
