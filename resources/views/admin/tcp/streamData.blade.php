{{-- resources/views/admin/tcp/streamData.blade.php --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight w-full">
        {{ __('🎧 TCP 即時數據') }}
    </h2>
@endsection
@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }

        .ctrl-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 0.5rem;
            z-index: 40;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .ctrl-overlay .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.2);
            z-index: 50;
        }

        .modal-container {
            position: absolute;
            top: 2.8rem;
            z-index: 60;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <div x-cloak wire:ignore x-data="{
        isCoten1Open: false,
        tcpButtonLabel: 'TCP: 載入中',
        scheduleButtonLabel: `擷取: {{ ($scheduleStatus['status'] ?? 'unknown') === 'running' ? ' ✅ 運行中 (每 ' . ($scheduleStatus['interval'] ?? '?') . '/H)' : (($scheduleStatus['status'] ?? 'unknown') === 'stopped' ? '❌ 已停止' : '狀態未知') }}`,
    }" @tcp-status_events.window="tcpButtonLabel = $event.detail.label"
        @schedule-status_events.window="scheduleButtonLabel = $event.detail.label" id="streamControlsComponent"
        class="max-w-7xl mx-auto my-0">
        <!-- 遮罩層 -->
        <div x-cloak x-show="isCoten1Open" @click="isCoten1Open = false;"></div>
        <!-- 控制按鈕 -->
        <button id="stbtn1" x-ref="stbtn1Ref"
            @click="isCoten1Open = !isCoten1Open; "
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <span x-text="isCoten1Open ? '隱藏 TCP 控制' : tcpButtonLabel"></span>
        </button>
        <div class="modal-container flex flex-col gap-4">
            <div id="coten1" x-ref="coten1PanelRef" class="w-auto" x-show="isCoten1Open" x-transition>
                @include('admin.tcp.modal.control', ['status' => $status ?? ''])
            </div>
        </div>
    </div>
    <div class="flex flex-col justify-center bg-gray-100 w-full h-full px-1">
        <div class="w-full bg-white bg-opacity-80 rounded-lg p-1">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto max-h-[calc(100vh-150px)]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col"
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">
                                    時間
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">
                                    機器名稱
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                                    Chip ID
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    進球
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    出球
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    投幣
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    回報值
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    分配點數
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    結算點數
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    紙幣面額
                                </th>
                                <th scope="col"
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[11%]">
                                    Token
                                </th>
                            </tr>
                        </thead>
                        <tbody id="stream-output-body"
                            class="bg-white divide-y divide-gray-200">
                            <tr id="waiting-stream-row">
                                <td colspan="11" class="px-2 py-4 text-center text-sm text-gray-500">
                                    等待數據流傳入...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const CONFIG = {
                MAX_RECORDS: 20,
                STORAGE_KEY: 'realtimeStreamData',
                API_ENDPOINTS: {
                    control: '/admin/tcp-server/control',
                    schedule: '/admin/tcp-server/control-schedule',
                    capture: '/admin/tcp-server/openDataGate',
                    status: '/admin/tcp-server/status'
                },
                CHANNELS: {
                    dataStream: '.DataReceived',
                    tcpStatus: '.TcpServerStatusEvent',
                    scheduleStatus: '.schedule.updated'
                }
            };

            const streamTableBody = document.getElementById('stream-output-body');
            let waitingRow = document.getElementById('waiting-stream-row');
            let currentData = loadDataFromStorage();

            function loadDataFromStorage() {
                try {
                    const data = localStorage.getItem(CONFIG.STORAGE_KEY);
                    return data ? JSON.parse(data) : [];
                } catch (e) {
                    console.error('從 localStorage 讀取失敗:', e);
                    return [];
                }
            }

            function saveDataToStorage(dataArray) {
                try {
                    localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(dataArray));
                } catch (e) {
                    console.error('寫入 localStorage 失敗:', e);
                }
            }

            function formatTimestamp(timestamp) {
                return new Date(timestamp).toLocaleString('zh-TW', {
                    month: 'numeric',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }); // 輸出：6/13 18:27
            }

            function renderData(dataArray) {
                streamTableBody.innerHTML = '';
                if (dataArray.length === 0) {
                    streamTableBody.innerHTML = `
                        <tr id="waiting-stream-row">
                            <td colspan="11" class="px-2 py-4 text-center text-sm text-gray-500">
                                等待數據流傳入...
                            </td>
                        </tr>`;
                    waitingRow = document.getElementById('waiting-stream-row');
                    return;
                }
                dataArray.forEach(item => {
                    const records = Array.isArray(item.data) ? item.data : [item.data];
                    records.forEach(record => {
                        
                        const row = document.createElement('tr');
                        row.className =
                            'bg-green-50 bg-opacity-30 transition-colors duration-1000';
                        row.innerHTML = `
                            <td class="px-2 py-2 text-xs text-gray-700">${formatTimestamp(item.timestamp)}</td>
                            <td class="px-2 py-2 text-xs text-gray-700">${record.machine_name || 'N/A'}</td>
                            <td class="px-2 py-2 text-xs text-gray-700">${record.chip_hardware_id || 'N/A'}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.ball_in ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.ball_out ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.credit_in ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.return_value ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.assign_credit ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.settled_credit ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-center text-gray-700">${record.bill_denomination ?? 0}</td>
                            <td class="px-2 py-2 text-xs text-gray-700 truncate" title="${record.auth_key || ''}">${record.auth_key || 'N/A'}</td>
                        `;
                        streamTableBody.prepend(row);

                        // console.log('record',record);

                        setTimeout(() => row.classList.remove('bg-green-50', 'dark:bg-green-900',
                            'bg-opacity-30', 'dark:bg-opacity-20'), 1500);
                    });
                });
                if (dataArray.length > CONFIG.MAX_RECORDS) {
                    currentData = dataArray.slice(0, CONFIG.MAX_RECORDS);
                    saveDataToStorage(currentData);
                }
            }

            renderData(currentData);

            if (window.Echo) {
                window.Echo.channel('tcpLiveEvent')
                    .listen('.DataReceived', (e) => {
                        // console.log('✅ [tcpLiveEvent.DataReceived] 收到數據:', JSON.stringify(e, null, 2));
                        const newData = {
                            data: e.data,
                            timestamp: e.timestamp || new Date().toISOString()
                        };
                        currentData.unshift(newData);
                        renderData(currentData);
                        saveDataToStorage(currentData);
                    });
                window.Echo.channel('tcp_server_status')
                    .listen('.TcpServerStatusEvent', (e) => {
                        console.log('✅ [tcp_server_status.TcpServerStatusEvent] 數據:',
                            JSON.stringify(e, null,
                                2));
                        if (e.status === 'pending') return;
                        updateTcpStatusDisplay(e.status);
                        updateTcpButtons(e.status);
                        hideLoadingOverlay('controllOverlay');
                    });

            } else {
                console.error('❌ Reverb 未初始化');
                if (waitingRow) waitingRow.cells[0].textContent = '錯誤：無法連接到即時數據服務';
            }

            const tcpStateMap = {
                stopped: '停止',
                running: '運行',
                restarting: '重啟',
                error: '錯誤',
                unknown: '狀態不明',
                loading: '載入中',
                terminated: '已終止'
            };

            function updateTcpStatusDisplay(status) {
                const redisStatusDiv = document.getElementById('redis_status');
                if (redisStatusDiv) {
                    redisStatusDiv.textContent = tcpStateMap[status] || '狀態不明';
                    redisStatusDiv.className = `inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold ${
                        status === 'running' ? 'bg-green-100 text-green-800' :
                        status === 'stopped' ? 'bg-red-100 text-red-800' :
                        status === 'error' ? 'bg-red-100 text-red-800' :
                        status === 'restarting' ? 'bg-blue-100 text-blue-800' :
                        status === 'loading' ? 'bg-yellow-100 text-yellow-800' :
                        status === 'terminated' ? 'bg-gray-100 text-gray-800' :
                        'bg-gray-100 text-gray-800'
                    }`;
                }
                window.dispatchEvent(new CustomEvent('tcp-status_events', {
                    detail: {
                        label: `TCP: ${tcpStateMap[status] || '狀態不明'}`
                    }
                }));
            }

            function updateTcpButtons(status) {
                const startBtn = document.getElementById('start-tcp');
                const stopBtn = document.getElementById('stop-tcp');
                if (startBtn) startBtn.disabled = status === 'running';
                if (stopBtn) stopBtn.disabled = status === 'stopped' || status === 'terminated';
            }

            async function fetchTcpStatus() {
                updateTcpStatusDisplay('loading');
                try {
                    const response = await fetch(CONFIG.API_ENDPOINTS.status, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-AUTH-TOKEN': '@php echo config('syswaw.tcp_api_key') @endphp'
                        }
                    });
                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || 'API 請求失敗');
                    }
                    const data = await response.json();
                    console.log('✅ 獲取 TCP 狀態：', data);
                    updateTcpStatusDisplay(data.status);
                    updateTcpButtons(data.status);
                    window.dispatchEvent(new CustomEvent('tcp-status_events', {
                        detail: {
                            label: `TCP: ${tcpStateMap[data.status] || '狀態不明'}`
                        }
                    }));
                } catch (error) {
                    console.error('獲取 TCP 狀態失敗：', error);
                    updateTcpStatusDisplay('error');
                }
            }

            window.triggerRedis = async (action) => {
                showLoadingOverlay('controllOverlay');
                try {
                    const response = await fetch(CONFIG.API_ENDPOINTS.control, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-AUTH-TOKEN': '@php echo config('syswaw.tcp_api_key') @endphp'
                        },
                        body: JSON.stringify({
                            action
                        })
                    });
                    if (!response.ok) throw await response.json();
                    console.log('TCP 指令成功:', await response.json());
                } catch (error) {
                    console.error('TCP 指令失敗:', error);
                    alert(`指令失敗: ${error.message || '請稍後重試'}`);
                } finally {
                    setTimeout(() => hideLoadingOverlay('controllOverlay'), 1000);
                }
            };

            updateTcpStatusDisplay('loading');
            fetchTcpStatus();

            document.addEventListener('click', (event) => {
                const component = document.getElementById('streamControlsComponent')?.__x;
                if (!component) return;
                const {
                    stbtn1Ref,
                    coten1PanelRef,
                    stbtn2Ref,
                    coten2PanelRef
                } = component.$refs;
                const modalOverlay = event.target.closest('.modal-overlay');
                if (modalOverlay) {
                    component.$data.isCoten1Open = false;
                    // component.$data.isCoten2Open = false;
                    return;
                }
                if (coten1PanelRef.contains(event.target) || coten2PanelRef.contains(event.target)) return;
                if (stbtn1Ref.contains(event.target) || stbtn2Ref.contains(event.target)) return;
                component.$data.isCoten1Open = false;
                // component.$data.isCoten2Open = false;
            });
        });

        function showLoadingOverlay(id) {
            const overlay = document.getElementById(id);
            if (overlay) overlay.style.display = 'flex';
        }

        function hideLoadingOverlay(id) {
            const overlay = document.getElementById(id);
            if (overlay) overlay.style.display = 'none';
        }

    </script>
@endpush
