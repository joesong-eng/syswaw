<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Tcp\TcpServerController;
use Illuminate\Http\Request;

class TcpScheduleCommand extends Command
{
    protected $signature = 'tcp:schedule {--function=}';
    // protected $signature = 'tcp:schedule';
    protected $description = '定時執行數據截取任務';


    public function handle()
    {
        $function = $this->option('function');

        if ($function === 'openDataGate') {
            $this->info('Opening data gate...');
            $controller = app()->make(TcpServerController::class);
            $controller->openDataGate(new Request());
            $this->info('Data gate opened.');
        }
    }
}
