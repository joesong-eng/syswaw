@extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900 w-full">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg ">
            <!-- 頁首 + 控制按鈕區 -->
            <div class="flex flex-wrap items-center justify-between mb-2 px-6 pt-6 w-full">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    {{ __('數據伺服器') }}
                </h2>
                <div id="btn-ctrl" class="flex flex-col items-end gap-2 ">
                    <div class="flex flex-wrap gap-2"><!-- 按鈕群組 -->
                        <button id="start-tcp" onclick="triggerRedis('start')"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-green-500">
                            {{ __('msg.start') }}
                        </button>
                        <button id="stop-tcp" onclick="triggerRedis('stop')"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-red-500">
                            {{ __('msg.stop') }}
                        </button>
                        <button id="restart-tcp" onclick="triggerRedis('restart')"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-blue-500">
                            {{ __('msg.restart') }}
                        </button>
                        <button id="status-tcp" onclick="triggerRedis('status')"
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded disabled:text-gray-500 disabled:hover:bg-yellow-500">
                            {{ __('msg.status') }}
                        </button>
                    </div>
                </div>
                <div class="text-sm text-right mt-0 w-full flex flex-col items-end">
                    <div id="redis_msg" class="mt-1 text-blue-600 dark:text-blue-400"></div>
                    <div id="redis_status" class="ms-1 mt-1 text-gray-700 dark:text-gray-200 font-medium">
                        @if ($status === 'running')
                            已啟動 ✅
                        @elseif ($status === 'stopped')
                            已停止 ❌
                        @else
                            目前狀態：未知
                        @endif
                    </div>
                </div>
                <!-- Redis 狀態訊息區 -->
            </div>
            <!-- WebSocket 測試（預設隱藏） -->
            <div class="hidden websock_test space-y-2">
                <p id="Reverb_status" class="text-gray-800 dark:text-gray-200">連線中...</p>
                <p id="Reverb_msg" class="text-gray-800 dark:text-gray-200">尚無訊息</p>
                <div class="text-right space-x-2">
                    <x-secondary-button onclick="triggerEvent()">Trigger Event</x-secondary-button>
                    <x-primary-button onclick="triggerBroadcast()" style="background-color: cadetblue;">Trigger
                        Broadcast</x-primary-button>
                </div>
            </div>
            {{-- 篩選表單 --}}
            <div class="container mx-auto px-2 pb-3 mt-4"> {{-- 增加一些上邊距 --}}
                <label for="machineIds" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Machine IDs（逗號分隔）:
                </label>
                <input type="text" name="machineIds" id="machineIds"
                    class="p-2 border rounded-md w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                    placeholder="例如：1,2,3">

                <div class="mt-2 text-right">
                    <button id="capture-data-btn"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('開門抓取數據') }}
                    </button>
                </div>
                <form id="filterForm" action="{{ route('admin.tcp-server') }}" method="GET"
                    class="flex flex-wrap items-center gap-4">
                    {{-- Arcade 篩選 --}}
                    <div>
                        <label for="arcade_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.arcade') }} / 店家:
                        </label>
                        <select name="arcade_id" id="arcade_id"
                            class="border rounded-md px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all">{{ __('msg.all') }} / 全部</option>
                            @foreach ($arcades as $arcade)
                                <option value="{{ $arcade->id }}"
                                    {{ (string) $filterArcadeId === (string) $arcade->id ? 'selected' : '' }}>
                                    {{ $arcade->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Machine 篩選 --}}
                    <div>
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.machine') }} / 機器:
                        </label>
                        {{-- 這裡可以考慮用 JS 動態更新選項，但先簡單列出所有機器或屬於選定 Arcade 的機器 --}}
                        {{-- 為了簡單起見，我們先列出所有機器，並使用 JS 在前端過濾選項 --}}
                        <select name="machine_id" id="machine_id"
                            class="border rounded-md px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all">{{ __('msg.all') }} / 全部</option>
                            @foreach ($machines as $machine)
                                {{-- 如果有 Arcade 篩選，只顯示該 Arcade 下的機器選項 --}}
                                @if (!$filterArcadeId || $filterArcadeId === 'all' || (string) $filterArcadeId === (string) $machine->arcade_id)
                                    <option value="{{ $machine->id }}"
                                        {{ (string) $filterMachineId === (string) $machine->id ? 'selected' : '' }}>
                                        {{ $machine->name }} (店舖: {{ $machine->arcade->name ?? '未知' }})
                                        {{-- 顯示機台名稱及所屬店舖 --}}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    {{-- 注意：由於使用了 JS 的 onchange 提交，不需要單獨的提交按鈕，除非你想額外添加 --}}
                </form>


            </div>
            {{-- 交易記錄 / 歷史數據列表 --}}
            <div id="transactions-list" class="container mx-auto px-2 pb-3">
                <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">抓取數據歷史記錄</h2>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-x-auto"> {{-- 考慮橫向滾動條 --}}
                    <div
                        class="flex items-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 p-2 min-w-max">
                        {{-- min-w-max 防止列寬被壓縮 --}}
                        <div class="w-[5%] text-center">ID</div> {{-- 序列號 --}}
                        <div class="w-[10%] text-center">店舖家</div>
                        <div class="w-[15%] text-center">機器名稱</div>
                        <div class="w-[15%] text-center">晶片 ID</div> {{-- 實際記錄的是晶片 ID --}}
                        <div class="w-[8%] text-center">進球數</div>
                        <div class="w-[8%] text-center">出球數</div>
                        <div class="w-[8%] text-center">投入金額</div>
                        <div class="w-[15%] text-center">抓取時間</div>
                        <div class="w-[16%] text-center">Token</div> {{-- 如果需要顯示 Token --}}
                    </div>

                    <div class="overflow-y-auto max-h-[calc(100vh-400px)] sm:max-h-[calc(100vh-350px)]">
                        {{-- 調整 max-height 以適應你的頁面佈局 --}}
                        @forelse ($records as $record)
                            <div
                                class="flex items-center my-2 mx-0 border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 p-2 min-w-max">
                                <div class="w-[5%] text-center">
                                    {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}</div>
                                {{-- 顯示分頁後的序列號 --}}
                                <div class="w-[10%] text-center">{{ $record->machine->arcade->name ?? '未知店舖' }}</div>
                                <div class="w-[15%] text-center">{{ $record->machine->name ?? '未知機器' }}</div>
                                <div class="w-[15%] text-center break-words">{{ $record->chip_id }}</div>
                                {{-- 顯示晶片 ID --}}
                                <div class="w-[8%] text-center">{{ $record->ball_in }}</div>
                                <div class="w-[8%] text-center">{{ $record->ball_out }}</div>
                                <div class="w-[8%] text-center">{{ $record->credit_in }}</div>
                                <div class="w-[15%] text-center">
                                    {{ $record->timestamp ? \Carbon\Carbon::parse($record->timestamp)->format('Y-m-d H:i:s') : 'N/A' }}
                                </div>
                                <div class="w-[16%] text-center break-words">{{ $record->token }}</div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                暫無歷史數據記錄。
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 分頁鏈接 --}}
                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const initialStatus = @json($status);
            updateRedisButtons(initialStatus); // ← 頁面載入時即刻設定
            if (window.Echo) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    document.getElementById('Reverb_status').innerText = '✅ 已連線 Reverb';
                });
                console.log('[Reverb] Echo 初始化完成');
                window.Echo.channel('channel-Reverb')
                    .listen('.event-Reverb', (e) => {
                        console.log('📡 廣播接收:', e);
                        document.getElementById('Reverb_msg').innerText = `✅ 收到：${e.message}`;
                        const initialStatus = @json($status);
                        console.log(initialStatus);
                        updateRedisButtons(initialStatus); // ← 頁面載入時即刻設定
                        hideLoadingOverlay();
                    });
            } else {
                console.error('❌ window.Echo 尚未初始化');
            }
        });

        window.triggerBroadcast = function() {
            const postData = {
                action: 'By Broadcast'
            };
            showLoadingOverlay();
            fetch('/admin/ethereal/broadcast', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(postData)
            }).then(response => response.json()).then(data => console.log('Response:', data)).catch(error => console
                .error('Error:', error));
        }
        window.triggerEvent = function() {
            showLoadingOverlay();
            const postData = {
                action: 'By Event'
            };
            fetch('/admin/ethereal/event', { //不會直接返回值,就只是廣播出去,
                    method: 'POST', // 明确指定 POST
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => console.log('Response:', data))
                .catch(error => console.error('Error:', error));
        }

        const xstate = {
            'stopped': '已停止 ❌',
            'running': '已啟動 ✅',
            'restarting': '已重新啟動 ✅'
        }
        document.addEventListener('DOMContentLoaded', () => {
            window.Echo.channel('redis-status-channel')
                .listen('.tcp.status.updated', (e) => {
                    console.log('✅ [指定事件] 收到: ', e);
                    document.getElementById('redis_status').innerText = xstate[e.status] || '未知狀態';
                    updateRedisButtons(e.status); // ← 加這行
                    document.getElementById('redis_msg').innerText =
                        `TCP 狀態：${e.status}，動作：${e.action}`; // 修改顯示內容
                    hideLoadingOverlay();

                })
        })
        const actionFeedback = {
            start: '啟動中...',
            stop: '停止中...',
            restart: '重啟中...',
            status: '查詢狀態中...'
        };

        window.triggerRedis = function(status) {
            showLoadingOverlay();
            // 即時顯示動作提示
            document.getElementById('redis_msg').innerText = actionFeedback[status] || '執行中...';
            const postData = {
                action: status
            };
            fetch('/admin/ethereal/redis_ctrl', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response:', data);
                    // 可選：這裡不要再處理顯示，由 Echo 廣播更新狀態
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('redis_msg').innerText = `❌ 請求失敗：${error.message}`;
                });
        }

        function updateRedisButtons(status) {
            document.getElementById('start-tcp').disabled = (status === 'running');
            document.getElementById('stop-tcp').disabled = (status === 'stopped');
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... 你現有的抓取指令 JavaScript 程式碼 ...
            const captureDataBtn = document.getElementById('capture-data-btn');
            const machineIdsInput = document.getElementById('machineIds');
            const redisMsgDiv = document.getElementById('redis_msg'); // 用來顯示訊息，你現有的元素

            if (captureDataBtn && machineIdsInput) {
                captureDataBtn.addEventListener('click', function() {
                    const machineIdsString = machineIdsInput.value.trim();

                    if (!machineIdsString) {
                        alert('請輸入機器 ID，多個 ID 請用逗號分隔。');
                        return;
                    }

                    // 將逗號分隔的字串轉換為數字陣列
                    const machineIds = machineIdsString.split(',').map(id => parseInt(id.trim())).filter(
                        id => !isNaN(id));

                    if (machineIds.length === 0) {
                        alert('請輸入有效的機器 ID。');
                        return;
                    }

                    // 禁用按鈕，避免重複點擊
                    captureDataBtn.disabled = true;
                    captureDataBtn.textContent = '發送中...';
                    if (redisMsgDiv) {
                        redisMsgDiv.textContent = '正在發送抓取指令...';
                    }


                    // 發送 POST 請求到 Laravel 後端 API
                    // 請根據你的路由定義確認正確的 URL
                    fetch('{{ route('admin.tcp-server.openDataGate') }}', { // 使用路由名稱生成 URL
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // 確保你有 CSRF token
                            },
                            body: JSON.stringify({
                                machine_ids: machineIds
                            }) // 將機器 ID 陣列作為 JSON 發送
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errorData => {
                                    throw new Error(errorData.message || '伺服器錯誤');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Success:', data);
                            if (redisMsgDiv) {
                                redisMsgDiv.textContent = data.message || '指令發送成功！';
                            }
                            // 成功發送指令後，可以考慮刷新頁面或使用廣播接收新數據並局部更新
                            alert(data.message || '指令發送成功！');
                            // 這裡暫時不自動刷新，等待 WebSocket 推送或手動刷新
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            if (redisMsgDiv) {
                                redisMsgDiv.textContent = '發送指令失敗: ' + error.message;
                            }
                            alert('發送指令失敗: ' + error.message);
                        })
                        .finally(() => {
                            captureDataBtn.disabled = false;
                            captureDataBtn.textContent = '開門抓取數據';
                        });
                });
            }


            // ====== Machine 篩選選項動態過濾 (可選功能，增加 UX) ======
            // 這個腳本用於根據選擇的 Arcade 過濾 Machine 下拉框的選項
            const arcadeSelect = document.getElementById('arcade_id');
            const machineSelect = document.getElementById('machine_id');
            // 將原始的 machine 選項保存下來
            const originalMachineOptions = Array.from(machineSelect.options);

            if (arcadeSelect && machineSelect) {
                arcadeSelect.addEventListener('change', function() {
                    const selectedArcadeId = this.value;

                    // 清空當前 Machine 選項 (保留 "全部" 選項)
                    machineSelect.innerHTML = '<option value="all">{{ __('msg.all') }} / 全部</option>';

                    if (selectedArcadeId === 'all') {
                        // 如果選擇 "全部 Arcade"，則恢復所有 Machine 選項 (除了 "全部")
                        originalMachineOptions.forEach(option => {
                            if (option.value !== 'all') {
                                machineSelect.appendChild(option.cloneNode(true));
                            }
                        });
                    } else {
                        // 如果選擇了特定的 Arcade，則只添加屬於該 Arcade 的 Machine 選項
                        const machinesData = @json($machines); // 將 $machines 數據從 PHP 傳遞到 JS

                        machinesData.forEach(machine => {
                            if (parseInt(machine.arcade_id) === parseInt(selectedArcadeId)) {
                                const option = document.createElement('option');
                                option.value = machine.id;
                                option.textContent = machine.name + ' (店舖: ' + (machine.arcade ?
                                        machine.arcade.name : '未知') +
                                    ')'; // 注意：這裡 arcade name 可能需要額外處理如果沒有預加載
                                // 設置選中狀態
                                if (parseInt(option.value) === parseInt(
                                        '{{ $filterMachineId }}')) {
                                    option.selected = true;
                                }
                                machineSelect.appendChild(option);
                            }
                        });
                    }
                    // 注意：這裡只是動態過濾前端選項，篩選的實際應用是在表單提交到後端後
                    // 如果你需要自動提交，onchange 事件已經處理了
                });

                // 初始化時觸發一次，確保 Machine 選項根據初始選定的 Arcade 正確顯示
                // 如果 $filterArcadeId 已經有值，這裡會過濾選項
                if (arcadeSelect.value !== 'all') {
                    // 手動構建 Machine 選項，因為 $machines 數據在 PHP 中已經過濾（如果 $filterArcadeId 有值的話）
                    // 為了和上面的邏輯一致，我們用 JS 重新根據 $machines 數據來構建
                    const selectedArcadeId = arcadeSelect.value;
                    machineSelect.innerHTML = '<option value="all">{{ __('msg.all') }} / 全部</option>';
                    const machinesData = @json($machines);
                    machinesData.forEach(machine => {
                        if (parseInt(machine.arcade_id) === parseInt(selectedArcadeId)) {
                            const option = document.createElement('option');
                            option.value = machine.id;
                            option.textContent = machine.name + ' (店舖: ' + (machine.arcade ? machine.arcade
                                .name : '未知') + ')';
                            if (parseInt(option.value) === parseInt('{{ $filterMachineId }}')) {
                                option.selected = true;
                            }
                            machineSelect.appendChild(option);
                        }
                    });
                } else {
                    // 如果初始沒有 Arcade 篩選，確保 Machine 選項是完整的 (除了 "全部")
                    machineSelect.innerHTML = '<option value="all">{{ __('msg.all') }} / 全部</option>';
                    originalMachineOptions.forEach(option => {
                        if (option.value !== 'all') {
                            machineSelect.appendChild(option.cloneNode(true));
                        }
                    });
                    // 設置初始 Machine 選中狀態
                    if ('{{ $filterMachineId }}' !== '' && '{{ $filterMachineId }}' !== 'all') {
                        machineSelect.value = '{{ $filterMachineId }}';
                    }

                }

                // 由於 onchange 事件已經提交表單，這個動態過濾只影響用戶看到的下拉框選項，
                // 實際的篩選邏輯仍然在後端處理。
                // 如果你想要更複雜的無頁面刷新篩選，需要使用 AJAX 替換表單提交。
            }

        });
    </script>
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const captureDataBtn = document.getElementById('capture-data-btn');
            const machineIdsInput = document.getElementById('machineIds');
            const redisMsgDiv = document.getElementById('redis_msg'); // 用來顯示訊息，你現有的元素
        
            if (captureDataBtn && machineIdsInput) {
                captureDataBtn.addEventListener('click', function () {
                    const machineIdsString = machineIdsInput.value.trim();
        
                    if (!machineIdsString) {
                        alert('請輸入機器 ID，多個 ID 請用逗號分隔。');
                        return;
                    }
        
                    // 將逗號分隔的字串轉換為數字陣列
                    const machineIds = machineIdsString.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
        
                    if (machineIds.length === 0) {
                         alert('請輸入有效的機器 ID。');
                         return;
                    }
        
                    // 禁用按鈕，避免重複點擊
                    captureDataBtn.disabled = true;
                    captureDataBtn.textContent = '發送中...';
                    if (redisMsgDiv) {
                         redisMsgDiv.textContent = '正在發送抓取指令...';
                    }
        
        
                    // 發送 POST 請求到 Laravel 後端 API
                    fetch('/api/tcp-server/open-data-gate', { // 請確認你的 API 路由
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // 如果你在 Web 路由中使用，需要 CSRF token
                        },
                        body: JSON.stringify({ machine_ids: machineIds }) // 將機器 ID 陣列作為 JSON 發送
                    })
                    .then(response => {
                        // 檢查 HTTP 狀態碼
                        if (!response.ok) {
                            // 如果響應不是 2xx，拋出錯誤
                            return response.json().then(errorData => {
                                 throw new Error(errorData.message || '伺服器錯誤');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // 處理成功響應
                        console.log('Success:', data);
                        if (redisMsgDiv) {
                             redisMsgDiv.textContent = data.message || '指令發送成功！';
                        }
                         alert(data.message || '指令發送成功！'); // 也可以用更友好的通知方式
                    })
                    .catch((error) => {
                        // 處理錯誤
                        console.error('Error:', error);
                        if (redisMsgDiv) {
                             redisMsgDiv.textContent = '發送指令失敗: ' + error.message;
                        }
                        alert('發送指令失敗: ' + error.message);
                    })
                    .finally(() => {
                        // 無論成功或失敗，都重新啟用按鈕
                        captureDataBtn.disabled = false;
                        captureDataBtn.textContent = '開門抓取數據';
                         // redisMsgDiv.textContent = ''; // 可以選擇清除訊息
                    });
                });
            }
        });
    </script> --}}
    {{-- <script>
            // document.addEventListener('DOMContentLoaded', function() {
                // const eventSource = new EventSource('/admin/tcp-server/stream');
                // const startTcpButton = document.getElementById('start-tcp');
                // const stopTcpButton = document.getElementById('stop-tcp');
                // const restartTcpButton = document.getElementById('restart-tcp');
                // const queryTcpButton = document.getElementById('query-tcp');
                // const msgStream = document.getElementById('msg-stream');
                // const controlButtons = [startTcpButton, stopTcpButton, restartTcpButton, queryTcpButton];
                // const localStorageKey = 'tcp_server_status';
        
                // // 頁面載入時觸發 restart 以獲取初始狀態
                // controlTcpServer('restart');    
                // eventSource.onmessage = (event) => {
                //     console.log('收到資料:', event.data);                
                //     try {
                //         const rawMessage = event.data;
                //         let message = rawMessage.trim();
                //         if (message.startsWith('"') && message.endsWith('"')) {
                //             message = message.substring(1, message.length - 1);
                //         }
                //         msgStream.textContent = '伺服器狀態: ' + message;
                //         localStorage.setItem(localStorageKey, message); // 將最新的狀態儲存到 localStorage
                //         updateButtonStates(message);
                //         hideSpinners();
                //     } catch (e) {
                //         // 如果資料不是 JSON，直接顯示純文字
                //         document.getElementById('messages').innerHTML += `<p>${event.data}</p>`;
                //     }
                // };
                // eventSource.onopen = function () {
                //     console.log('連線成功');
                // };
                // eventSource.onerror = (error) => {
                //     console.log('EventSource 錯誤:', error);
                //     hideSpinners();
                // };
        
                // function controlTcpServer(action, buttonElement = null) { // 修改函數，接受 buttonElement 參數
                //     disableButtons();
                //     let buttonToSpin = buttonElement;
                //     if (!buttonToSpin) {
                //         buttonToSpin = document.getElementById('restart-tcp'); // 如果沒有傳遞按鈕元素，預設使用 restart 按鈕
                //     }
                //     showSpinner(buttonToSpin);
                //     fetch(`/admin/tcp-server/${action}`, {
                //         method: 'POST',
                //         headers: {
                //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                //         }
                //     }).then(response => {
                //         if (!response.ok) {
                //             console.error(`Error during ${action}:`, response.status);
                //             hideSpinners();
                //         }
                //     }).catch(error => {
                //         console.error(`Error during ${action}:`, error);
                //         hideSpinners();
                //     });
                // }
        
                // startTcpButton.addEventListener('click', function() { controlTcpServer('start',startTcpButton); });
                // stopTcpButton.addEventListener('click', function() { controlTcpServer('stop',stopTcpButton); });
                // restartTcpButton.addEventListener('click', function() { controlTcpServer('restart',restartTcpButton); });
        
                // queryTcpButton.addEventListener('click', function() {
                //     showSpinner(this);
                //     fetch('/admin/tcp-server/query-ids', {
                //         method: 'GET'
                //     }).then(response => response.json())
                //     .then(data => {
                //         console.log('Machine IDs:', data);
                //         // 在這裡處理查詢到的 Machine IDs，例如顯示在頁面上
                //         hideSpinners();
                //     }).catch(error => {
                //         console.error('Error querying Machine IDs:', error);
                //         hideSpinners();
                //     });
                // });
                // function updateButtonStates(status) {
                //     enableButtons(); // 每次更新前先啟用所有按鈕
                //     if (status === 'running') {
                //         startTcpButton.disabled = true;
                //         startTcpButton.classList.add('cursor-not-allowed');
                //     } else if (status === 'stopped') {
                //         stopTcpButton.disabled = true;
                //         stopTcpButton.classList.add('cursor-not-allowed');
                //         restartTcpButton.disabled = true;
                //         restartTcpButton.classList.add('cursor-not-allowed');
                //     }
                // }
                // function disableButtons() {
                //     controlButtons.forEach(button => {
                //         button.disabled = true;
                //         button.classList.add('cursor-not-allowed');
                //     });
                // }
                // function enableButtons() {
                //     controlButtons.forEach(button => {
                //         button.disabled = false;
                //         button.classList.remove('cursor-not-allowed');
                //     });
                // }
                // function showSpinner(button) {
                //     button.classList.add('relative'); // 讓按鈕成為相對定位的容器
                //     const spinner = document.createElement('span');
                //     spinner.classList.add('absolute', 'animate-spin', 'w-4', 'h-4', 'border-2', 'border-gray-300', 'border-t-green-500', 'rounded-full');
                //     spinner.setAttribute('id', button.id + '-spinner');
                //     spinner.style.top = '50%';
                //     spinner.style.left = '50%';
                //     spinner.style.transform = 'translate(-50%, -50%)';
                //     button.appendChild(spinner); // 將 spinner 添加為按鈕的子元素
                // }
                // function hideSpinners() {
                //     controlButtons.forEach(button => {
                //         const spinner = document.getElementById(button.id + '-spinner');
                //         if (spinner) {
                //             spinner.remove();
                //         }
                //     });
                // }
            // });
        </script> --}}
@endpush
