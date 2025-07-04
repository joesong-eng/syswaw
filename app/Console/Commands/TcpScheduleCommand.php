<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Tcp\TcpServerController;
use Illuminate\Http\Request;

class TcpScheduleCommand extends Command
{
    protected $signature = 'tcp:schedule';
    protected $description = '定时执行数据截取任务';

    public function handle()
    {
        $this->info('Opening data gate...');
        $controller = new TcpServerController();
        $controller->openDataGate(new \Illuminate\Http\Request());
        $this->info('Data gate opened.');
    }
}
