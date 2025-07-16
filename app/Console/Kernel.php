<?php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log; // 引入 Log facade
use App\Http\Controllers\Tcp\TcpServerController;
use App\Http\Controllers\DataIngestionController; // 引入新的控制器
use App\Events\TcpScheduleState; // <--- 引入你的事件
use Illuminate\Http\Request; // <--- 引入 Request 類

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 每月1號的 00:10 執行月結報告
        $schedule->command('reports:generate-monthly')->monthlyOn(1, '00:10');

        // 將數據擷取任務改為每小時執行，以便測試
        $schedule->call(function () {
            // 手動實例化控制器並調用方法，確保 Request 依賴被處理
            app(DataIngestionController::class)->ingestMqttData(new Request());
        })
            // ->everyFiveMinutes()
            ->hourly()
            ->name('ingest_mqtt_data_task') // 添加唯一的任務名稱
            ->withoutOverlapping()
        ;
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
