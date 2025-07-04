<!-- {{-- 
發送：
    此tcp server控制器 配合TcpServer@control
    執行redis廣播：
    Redis::publish('redis_ctrlTcpServer', $action);
    讓python接收廣播消息後
    觸發tcp-main.py@main()：
    根據參數如start  控制tcp-server.py@start_server()啟動｜停止｜重啟
blade (ajax)->controller (redis)->python -> blade (reverb)

接收：
    執行完 啟動｜停止｜重啟 
    啟動完已訪問laravel api：
     https://sxs.tg25.win/api/tcp-status 觸發laravel reverb 
    以事件方式 event(new TcpServerState($status, $action));
    推送回到前端
python(requests.post)-> controller (reverb)->blade
依賴 app/Events/TcpServerState 事件 把接收到的事件
@props(['status']) {{-- 確保接收 status --}} -->

<div class="relative w-full p-4 bg-indigo-100 rounded-lg shadow h-full"> {{-- 移除 mt-4, 添加 h-full 使高度一致 --}}
    {{-- 主要內容區域 --}}
    <div id="card-ctrl-control" class="card-ctrl space-y-4 pt-5 hidden"> {{-- 使用 space-y 提供垂直間距, 添加 ID --}}
        <div id="btn-ctrl" class="flex flex-wrap gap-2 items-center justify-center">
            <x-button id="start-tcp" onclick="triggerRedis('start')"
                class="inline-flex items-center justify-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 disabled:opacity-50 transition ease-in-out duration-150">
                {{ __('msg.start') }}
            </x-button>
            <x-button id="stop-tcp" onclick="triggerRedis('stop')"
                class="inline-flex items-center justify-center px-4 py-2 bg-orange-500 hover:bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 transition ease-in-out duration-150">
                {{ __('msg.stop') }}
            </x-button>
            <x-button id="restart-tcp" onclick="triggerRedis('restart')"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition ease-in-out duration-150">
                {{ __('msg.restart') }}
            </x-button>
            <x-button id="status-tcp" onclick="triggerRedis('status')"
                class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 transition ease-in-out duration-150">
                {{ __('msg.status') }}
            </x-button>
        </div>
    </div>
    <!-- 狀態顯示 (絕對定位) -->
    <div id="ctrl-card-btn-control" class="ctrl-card-btn absolute top-1 right-4 text-sm text-right cursor-pointer">
        {{-- 調整 top/right 邊距, 添加 ID 和 cursor-pointer --}}
        <span class="text-lg font-semibold mb-4 text-gray-800">TCP server</span>
        <span id="redis_status"
            class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">
            {{-- 由 Blade 初始渲染狀態，由 JS 廣播更新 --}}
            @if ($status === 'running')
                已啟動 ✅
            @elseif ($status === 'stopped')
                已停止 ❌
            @else
                目前狀態：未知
            @endif
        </span>
    </div>
    <div id="controllOverlay" class="ctrl-overlay">
        <div class="spinner"></div>
    </div>
</div>
