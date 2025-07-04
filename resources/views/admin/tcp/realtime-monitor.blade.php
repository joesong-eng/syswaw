{{-- resources/views/admin/realtime-monitor.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex justify-center bg-gray-100 w-full h-full p-4 sm:p-6">
        <div
            class="w-full max-w-6xl bg-white bg-opacity-80 shadow-lg rounded-lg p-4 sm:p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="bi bi-broadcast mr-2"></i> 即時 數據 監控
            </h2>

            {{-- 數據顯示區域 --}}
            <div class="bg-gray-50 rounded-lg shadow-inner p-4 min-h-[calc(100vh-200px)]">
                {{-- 標題列 --}}
                <div
                    class="flex items-center border-b border-gray-300 pb-2 mb-2 text-sm font-medium text-gray-600 sticky top-0 bg-gray-50 z-10">
                    <div class="w-[15%] px-2">店家</div>
                    <div class="w-[20%] px-2">機器名稱</div>
                    <div class="w-[10%] px-2 text-center">進球</div>
                    <div class="w-[10%] px-2 text-center">出球</div>
                    <div class="w-[10%] px-2 text-center">投幣</div>
                    <div class="w-[15%] px-2 text-center">晶片 ID</div>
                    <div class="w-[20%] px-2 text-center">更新時間</div>
                </div>

                {{-- 動態數據列表容器 --}}
                <div id="realtime-data-list" class="space-y-1 overflow-y-auto max-h-[calc(100vh-260px)]">
                    {{-- 初始提示訊息 --}}
                    <p id="waiting-message" class="text-center text-gray-500 py-4">
                        <i class="bi bi-hourglass-split mr-1"></i> 等待即時數據傳入...
                    </p>
                    {{-- JavaScript 會在這裡插入數據 --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dataListContainer = document.getElementById('realtime-data-list');
            let waitingMessage = document.getElementById('waiting-message'); // 可變，因為會被移除
            const MAX_ITEMS_DISPLAYED = 100; // 最多顯示多少條記錄

            if (!window.Echo) {
                console.error('❌ Laravel Echo (Reverb) 未初始化，無法接收即時數據。請檢查 bootstrap.js 和 Reverb 配置。');
                if (waitingMessage) waitingMessage.textContent = '錯誤：無法連接即時數據服務。';
                return;
            }

            console.log('🎧 正在監聽即時數據頻道...');

            // *** 重要：請確保這裡的頻道和事件名稱與你 Laravel 後端廣播時使用的名稱一致 ***
            const channelName = 'realtime-machine-data'; // 假設的頻道名稱
            const eventName = '.MachineDataUpdated'; // 假設的事件名稱 (包含前面的 '.')

            window.Echo.channel(channelName)
                .listen(eventName, (event) => {
                    console.log(`✅ [${channelName}${eventName}] 收到數據: `, event);

                    // 收到第一條數據時移除等待訊息
                    if (waitingMessage) {
                        waitingMessage.remove();
                        waitingMessage = null; // 設為 null 避免後續再次嘗試移除
                    }

                    // 假設事件物件的 'data' 屬性包含我們需要的機器數據
                    const machineData = event.data;

                    if (!machineData) {
                        console.warn('收到的事件中缺少有效的 data 屬性', event);
                        return;
                    }
                    // 創建新的數據行元素
                    const row = document.createElement('div');
                    row.className =
                        'flex items-center border-b border-gray-200 py-1.5 text-sm text-gray-800 animate-pulse bg-green-50 bg-opacity-50'; // 添加短暫高亮效果

                    // 填充數據行內容
                    row.innerHTML = `
                    <div class="w-[15%] px-2 truncate">${machineData.arcade_name || '未知店家'}</div>
                    <div class="w-[20%] px-2 truncate">${machineData.machine_name || '未知機器'}</div>
                    <div class="w-[10%] px-2 text-center">${machineData.ball_in ?? 0}</div>
                    <div class="w-[10%] px-2 text-center">${machineData.ball_out ?? 0}</div>
                    <div class="w-[10%] px-2 text-center">${machineData.credit_in ?? 0}</div>
                    <div class="w-[15%] px-2 text-center text-xs truncate" title="${machineData.chip_id || ''}">${machineData.chip_id || 'N/A'}</div>
                    <div class="w-[20%] px-2 text-center text-xs c-list">${formatTimestamp(machineData.timestamp)}</div>
                `;

                    // 將新數據行插入到列表頂部
                    dataListContainer.prepend(row);

                    // 短暫高亮後移除效果
                    setTimeout(() => {
                        row.classList.remove('animate-pulse', 'bg-green-50', 'dark:bg-green-900',
                            'bg-opacity-50', 'dark:bg-opacity-30');
                    }, 1500); // 1.5秒後移除高亮

                    // 限制顯示的記錄數量，移除舊的記錄
                    while (dataListContainer.children.length > MAX_ITEMS_DISPLAYED) {
                        dataListContainer.removeChild(dataListContainer.lastChild);
                    }
                });

            // 可以在這裡添加連接狀態的監聽，提供更好的用戶反饋
            window.Echo.connector.pusher.connection.bind('connected', () => {
                console.log('🔗 已成功連接到 Reverb 服務。');
                if (waitingMessage) waitingMessage.innerHTML =
                    '<i class="bi bi-check-circle-fill text-green-500 mr-1"></i> 已連接，等待即時數據傳入...';
            });

            window.Echo.connector.pusher.connection.bind('error', (err) => {
                console.error('❌ 連接到 Reverb 時發生錯誤:', err);
                if (waitingMessage) waitingMessage.innerHTML =
                    `<i class="bi bi-exclamation-triangle-fill text-red-500 mr-1"></i> 連接錯誤: ${err.error?.data?.message || '無法連接服務'}`;
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                console.warn('🔌 與 Reverb 服務的連接已斷開。');
                if (waitingMessage) waitingMessage.innerHTML =
                    '<i class="bi bi-wifi-off mr-1"></i> 連接已斷開，嘗試重新連接...';
            });

        });
    </script>
@endpush
