{{-- /www/wwwroot/syswaw/resources/views/admin/tcp/index.blade.php --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.history') }}
    </h2>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto relative">
        <!-- 按鈕 -->
        {{-- <div id="captureOverlay" class="ctrl-overlay">
            <div class="spinner"></div>
        </div> --}}
        <div class="flex justify-end">
            <button id="capture-data-btn" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                {{ __('msg.current_data') }}
            </button>
        </div>
        <form id="filterForm">
            <!-- 表格容器 -->
            <div class="relative bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="table-container max-h-[calc(100vh-11rem)] lg:max-h-[calc(100vh-11rem)] overflow-y-auto">
                    <table class="w-full table-fixed border-collapse">
                        <thead class="bg-gray-100">
                            <tr class="text-sm font-medium text-gray-700">
                                <th class="hidden lg:table-cell sticky top-0 bg-gray-100 z-10"
                                    style="width: 60px; min-width: 60px; max-width: 60px;">
                                    <div class="px-1 py-2 text-center">ID</div>
                                </th>
                                <!-- 店家與機器選擇欄位 (合併為一個 th) -->
                                <th class="px-1 py-2 sticky top-0 bg-gray-100 z-10" colspan="2">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <!-- 店家選擇 -->
                                        <div class="flex-1">
                                            <select name="arcade_id" id="arcade_id"
                                                onchange="showLoadingOverlay(); document.getElementById('filterForm').submit();"
                                                class="border-b border-gray-300
                                                w-full bg-transparent text-center cursor-pointer hover:text-blue-500 text-sm appearance-none">
                                                <option value="all"
                                                    {{ (string) $filterArcadeId === 'all' ? 'selected' : '' }}>店家</option>
                                                @foreach ($arcades as $arcade)
                                                    <option value="{{ $arcade->id }}"
                                                        {{ (string) $filterArcadeId === (string) $arcade->id ? 'selected' : '' }}>
                                                        {{ $arcade->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- 機器選擇 -->
                                        <div class="flex-1">
                                            <select name="machine_id" id="machine_id"
                                                onchange="showLoadingOverlay(); document.getElementById('filterForm').submit();"
                                                class="w-full bg-transparent text-center cursor-pointer text-sm appearance-none">

                                                <option value="all">{{ __('msg.all') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </th>
                                <!-- ball_in/ball_out -->
                                <th class="px-1 py-2 text-center sticky top-0 bg-gray-100 z-10">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">{{ __('msg.ball_in') }}</div>
                                        <div class="flex-1 text-center">{{ __('msg.ball_out') }}</div>
                                    </div>
                                </th>
                                <!-- credit_in/coin_out -->
                                <th class="px-1 py-2 text-center sticky top-0 bg-gray-100 z-10">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">{{ __('msg.coin') }}</div>
                                        <div class="flex-1 text-center">{{ __('msg.coin_out') }}</div>
                                    </div>
                                </th>
                                <th class="px-0 py-0 text-center sticky top-0 bg-gray-100 z-10">
                                    {{ __('msg.bill_denomination') }}
                                </th>
                                <!-- assign_credit/settled_credit -->
                                <th class="px-1 py-2 text-center sticky top-0 bg-gray-100 z-10">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">{{ __('msg.assign_credit') }}</div>
                                        <div class="flex-1 text-center">{{ __('msg.settled_credit') }}</div>
                                    </div>
                                </th>
                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-10">
                                    <select name="time_filter" id="time_filter"
                                        onchange="showLoadingOverlay(); document.getElementById('filterForm').submit();"
                                        class="w-full bg-transparent text-center cursor-pointer hover:text-blue-500 text-sm appearance-none">
                                        <option value="all"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'all' ? 'selected' : '' }}>
                                            {{ __('msg.all') }} {{ __('msg.time') }}
                                        </option>
                                        <option value="today"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'today' ? 'selected' : '' }}>
                                            {{ __('msg.today') }}
                                        </option>
                                        <option value="yesterday"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'yesterday' ? 'selected' : '' }}>
                                            {{ __('msg.yesterday') }}
                                        </option>
                                        <option value="last_3_days"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'last_3_days' ? 'selected' : '' }}>
                                            {{ __('msg.last_3_days') }}
                                        </option>
                                        <option value="last_7_days"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'last_7_days' ? 'selected' : '' }}>
                                            {{ __('msg.last_7_days') }}
                                        </option>
                                        <option value="this_week"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'this_week' ? 'selected' : '' }}>
                                            {{ __('msg.this_week') }}
                                        </option>
                                        <option value="last_week"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'last_week' ? 'selected' : '' }}>
                                            {{ __('msg.last_week') }}
                                        </option>
                                        <option value="this_month"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'this_month' ? 'selected' : '' }}>
                                            {{ __('msg.this_month') }}
                                        </option>
                                        <option value="last_month"
                                            {{ (string) ($filterTimeRange ?? 'all') === 'last_month' ? 'selected' : '' }}>
                                            {{ __('msg.last_month') }}
                                        </option>
                                    </select>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($records as $record)
                                <tr class="text-sm text-gray-700 hover:bg-gray-50">
                                    <td class="hidden lg:table-cell text-center"
                                        style="width: 60px; min-width: 60px; max-width: 60px;">
                                        <div class="px-1 py-2">
                                            {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}
                                        </div>
                                    </td>
                                    <!-- 店家與機器資訊欄位 (合併為一個 td) -->


                                    <td class="px-1 py-2" colspan="2">
                                        <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                            <!-- 店家名稱 -->
                                            <div class="flex-1 text-center">
                                                <div class="truncate">
                                                    {{ $record->arcade->name ?? '未知店铺' }}
                                                </div>
                                            </div>
                                            <!-- 機器名稱 -->
                                            <div class="flex-1 text-center">
                                                <div class="truncate">
                                                    {{ $record->machine->name ?? '未知机器' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- ball_in/ball_out -->
                                    <td class="px-1 py-2 text-center">
                                        <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                            <div class="flex-1 text-center">{{ $record->ball_in }}</div>
                                            <div class="flex-1 text-center">{{ $record->ball_out }}</div>
                                        </div>
                                    </td>
                                    <!-- credit_in/coin_out -->
                                    <td class="px-1 py-2 text-center">
                                        <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                            <div class="flex-1 text-center">
                                                {{ $record->credit_in }}</div>
                                            <div class="flex-1 text-center">
                                                {{ $record->coin_out }}</div>
                                        </div>
                                    </td>
                                    <td class="px-1 py-2 text-center">{{ $record->bill_denomination }}</td>
                                    <td class="px-1 py-2 text-center">
                                        <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                            <div class="flex-1 text-center">
                                                {{ $record->assign_credit }}</div>
                                            <div class="flex-1 text-center">
                                                {{ $record->settled_credit }}</div>
                                        </div>
                                    </td>
                                    <td class="px-1 py-2 text-center text-xs">
                                        @if ($record->timestamp)
                                            {{ \Carbon\Carbon::parse($record->timestamp)->timezone('Asia/Taipei')->format('ymd H:i:s') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                        {{ __('暫無歷史紀錄') }}。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <!-- 分頁 -->
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </div>
@endsection
@push('style')
    <style>
        /* 遮罩樣式 */
        /* .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.2);
                z-index: 50;
            } */

        /* .ctrl-overlay {
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
                } */

        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
@endpush
@push('scripts')
    <script>
        // streamControls 函數簡化，不再需要處理 scheduleButtonLabel 和 initialScheduleStatus
        function streamControls() { // 移除參數
            return {
                isCoten2Open: false,
                // issettingsOpen: false, // 移除，因為相關 UI 已移除
                // scheduleButtonLabel: initialScheduleButtonLabel, // 移除
                // initialScheduleStatus: initialScheduleStatus, // 移除
                init() {
                    // init() 函數體也可能變得簡單或空，因為它處理的狀態管理部分已移除
                    // this.initPanelStatus(); // 移除
                },
                // updateScheduleUI 方法已不再由 Alpine.js 組件管理，因為排程狀態顯示已移除
                // updateScheduleUI(status) { /* ... */ }, // 移除
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const allMachinesData = @json($machines->load('arcade') ?? []);
            const initialFilterArcadeId = @json($filterArcadeId ?? 'all');
            const initialFilterMachineId = @json($filterMachineId ?? 'all');

            const filterForm = document.getElementById('filterForm');
            const arcadeFilterSelect = document.getElementById('arcade_id');
            const machineFilterSelect = document.getElementById('machine_id');

            const populateMachineFilterSelect = (selectedArcadeId, initialMachineId = null) => {
                if (!machineFilterSelect) return;

                machineFilterSelect.innerHTML = '<option value="all">{{ __('msg.all') }}</option>';

                allMachinesData.forEach(machine => {
                    if (selectedArcadeId === 'all' || parseInt(machine.arcade_id) === parseInt(
                            selectedArcadeId)) {
                        const option = document.createElement('option');
                        option.value = machine.id;
                        option.textContent = `${machine.name}`;
                        machineFilterSelect.appendChild(option);
                    }
                });

                if (initialMachineId && initialMachineId !== 'all') {
                    const optionToSelect = machineFilterSelect.querySelector(
                        `option[value="${initialMachineId}"]`);
                    if (optionToSelect) {
                        optionToSelect.selected = true;
                    }
                }
            };

            if (arcadeFilterSelect && machineFilterSelect) {
                arcadeFilterSelect.addEventListener('change', function() {
                    const selectedArcadeId = this.value;
                    populateMachineFilterSelect(selectedArcadeId);
                });

                populateMachineFilterSelect(initialFilterArcadeId, initialFilterMachineId);
            } else if (machineFilterSelect) {
                populateMachineFilterSelect('all', initialFilterMachineId);
            }

            // 遮罩函數，保留因為手動擷取可能還會用到
            // window.showLoadingOverlay = (overlayId = 'captureOverlay') => {
            //     const overlay = document.getElementById(overlayId);
            //     if (overlay) overlay.style.display = 'flex';
            // };
            // const hideLoadingOverlay = (overlayId = 'captureOverlay') => {
            //     const overlay = document.getElementById(overlayId);
            //     if (overlay) overlay.style.display = 'none';
            // };

            // 以下 TCP 服務控制相關的變數和函數，如果其用途是控制 tcp:schedule，則可移除
            // 如果它們用於控制 TCP 服務本身（如 Socket 伺服器），則需要保留
            // 由於你現在專注於 00:00 的數據擷取，David 暫時保留，但如果確認只和 tcp:schedule 有關，則可移除。
            const initialStatus = @json($status); // 這個 $status 變數可能仍來自 TCP 服務狀態而非定時擷取
            const redisStatusDiv = document.getElementById('redis_status');
            const startBtn = document.getElementById('start-tcp');
            const stopBtn = document.getElementById('stop-tcp');
            const restartBtn = document.getElementById('restart-tcp');
            const statusBtn = document.getElementById(
                'status-tcp'); // 這個按鈕在 modal/capture.blade.php，如果該模態被移除，這裡就沒有用

            const tcpStateMap = {
                'stopped': '已停止 ❌',
                'running': '已啟動 ✅',
                'restarting': '已重新啟動 ✅'
            };

            function updateTcpStatusDisplay(status) {
                if (redisStatusDiv) {
                    redisStatusDiv.textContent = tcpStateMap[status] || '未知狀態';
                    if (status === 'running') {
                        redisStatusDiv.className =
                            'inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold bg-green-100 text-green-800';
                    } else if (status === 'stopped') {
                        redisStatusDiv.className =
                            'inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold bg-red-100 text-red-800';
                    } else {
                        redisStatusDiv.className =
                            'inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold bg-gray-100 text-gray-800';
                    }
                }
            }

            function updateTcpButtons(status) {
                if (startBtn) startBtn.disabled = (status === 'running');
                if (stopBtn) stopBtn.disabled = (status === 'stopped');
            }

            updateTcpStatusDisplay(initialStatus);
            updateTcpButtons(initialStatus);

            window.triggerRedis = function(action) {
                showLoadingOverlay('controllOverlay');
                fetch('/admin/tcp-server/control', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: action
                        })
                    })
                    .then(response => response.ok ? response.json() : response.json().then(err => Promise
                        .reject(err)))
                    .then(data => {
                        console.log('TCP Control Command Response:', data);
                        // 狀態更新將透過 Reverb 監聽器處理
                    })
                    .catch(error => {
                        console.error('TCP Control Command Error:', error);
                        hideLoadingOverlay('controllOverlay');
                        Swal.fire('錯誤', `TCP 控制命令失敗: ${error.message || '請檢查日誌'}`, 'error');
                    });
            };

            // 以下定時任務控制相關的變數和函數，如果其用途是控制 tcp:schedule，則可移除
            // 由於你現在專注於 00:00 的數據擷取，這些按鈕也不再需要
            // const initialScheduleStatus = @json($scheduleStatus ?? ['status' => 'stopped', 'interval' => null]); // 移除
            // const intervalSelect = document.getElementById('schedule_interval'); // 移除
            // const startScheduleBtn = document.getElementById('start-schedule'); // 移除
            // const stopScheduleBtn = document.getElementById('stop-schedule'); // 移除
            // const scheduleStatusDisplay = document.getElementById('schedule_status_display'); // 移除

            // updateScheduleUI 函數不再需要，因為定時任務狀態不再在前端顯示控制
            // function updateScheduleUI(statusData) { /* ... */ } // 移除

            // updateScheduleUI(initialScheduleStatus); // 移除

            // window.triggerSchedule 函數不再需要
            // window.triggerSchedule = function(action) { /* ... */ }; // 移除

            // 手動擷取按鈕和其邏輯仍保留
            const captureDataBtn = document.getElementById('capture-data-btn');

            function sendCaptureTrigger() {
                console.log('sendCaptureTrigger called');
                if (captureDataBtn) captureDataBtn.disabled = true;
                showLoadingOverlay('loadingOverlay');
                fetch('{{ route('latestMqttData') }}', {
                        method: 'GET', // 更改為 GET 請求
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.ok ? response.json() : response.json().then(err => Promise.reject(
                        err)))
                    .then(data => {
                        console.log('Capture Trigger Success:', data);
                        updateTableWithMqttData(data); // 呼叫新函式更新表格
                        // 在這裡添加延遲
                        // setTimeout(() => {
                        hideLoadingOverlay('loadingOverlay');
                        // }, 2000); // 延遲 500 毫秒
                    })
                    .catch(error => {
                        console.error('Capture Trigger Error:', error);
                        Swal.fire('錯誤', `擷取失敗: ${error.error || '請檢查日誌'}`, 'error');
                        setTimeout(() => { // 錯誤時也延遲隱藏
                            hideLoadingOverlay('captureOverlay');
                        }, 500);
                    })
                    .finally(() => {
                        if (captureDataBtn) captureDataBtn.disabled = false;
                    });

            }

            if (captureDataBtn) {
                captureDataBtn.addEventListener('click', sendCaptureTrigger);
            }

            // 新增函式：更新表格內容
            function updateTableWithMqttData(mqttData) {
                const tbody = document.querySelector('.table-fixed tbody');
                if (!tbody) {
                    console.error('Table body not found.');
                    return;
                }

                tbody.innerHTML = ''; // 清空現有內容

                if (mqttData.length === 0) {
                    const noRecordsRow = document.createElement('tr');
                    noRecordsRow.innerHTML = `
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                {{ __('暫無歷史紀錄') }}。
                            </td>
                        </tr>
                    `;
                    tbody.appendChild(noRecordsRow);
                    return;
                }

                mqttData.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.className = 'text-sm text-gray-700 hover:bg-gray-50';

                    // 格式化時間戳
                    const formattedTimestamp = item.timestamp ? new Date(item.timestamp).toLocaleString(
                        'zh-TW', {
                            year: '2-digit',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).replace(/\//g, '') : 'N/A';

                    row.innerHTML = `
                        <td class="hidden lg:table-cell text-center" style="width: 60px; min-width: 60px; max-width: 60px;">
                            <div class="px-1 py-2">${index + 1}</div>
                        </td>
                        <td class="px-1 py-2" colspan="2">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">
                                    <div class="truncate">${item.arcade_name || '未知店铺'}</div>
                                </div>
                                <div class="flex-1 text-center">
                                    <div class="truncate">${item.machine_name || '未知机器'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-1 py-2 text-center">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">${item.data.ball_in || 0}</div>
                                <div class="flex-1 text-center">${item.data.ball_out || 0}</div>
                            </div>
                        </td>
                        <td class="px-1 py-2 text-center">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">${item.data.credit_in || 0}</div>
                                <div class="flex-1 text-center">${item.data.coin_out || 0}</div>
                            </div>
                        </td>
                        <td class="px-1 py-2 text-center">${item.data.bill_denomination || 0}</td>
                        <td class="px-1 py-2 text-center">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">${item.data.assign_credit || 0}</div>
                                <div class="flex-1 text-center">${item.data.settled_credit || 0}</div>
                            </div>
                        </td>
                        <td class="px-1 py-2 text-center text-xs">
                            ${formattedTimestamp}
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            // Reverb 監聽器也需簡化
            if (window.Echo) {
                // 如果 TCP 服務狀態仍然需要實時顯示，保留這個監聽
                window.Echo.channel('reverb_tcpSvRespone')
                    .listen('.tcpSvRespone.updated', (e) => {
                        console.log('✅ [tcpSvRespone.updated] 收到 TCP 狀態: ', e);
                        updateTcpStatusDisplay(e.status);
                        updateTcpButtons(e.status);
                        hideLoadingOverlay('controllOverlay');
                    });

                // 由於不再有前端控制的定時任務狀態，這個監聽器可以移除
                // window.Echo.channel('reverb_tcpScheduleResponse')
                //     .listen('.schedule.updated', (e) => {
                //         console.log('✅ [schedule.updated] 收到定時狀態: ', e);
                //         window.dispatchEvent(new CustomEvent('schedule-status_events', {
                //             detail: e
                //         }));
                //         hideLoadingOverlay('captureOverlay');
                //     });

                console.log('[Reverb] Echo 初始化完成 (TCP 監聽，定時任務監聽已移除)');
            } else {
                console.error('❌ window.Echo 尚未初始化');
                if (redisStatusDiv) updateTcpStatusDisplay('error');
                // if (scheduleStatusDisplay) updateScheduleUI({ status: 'error', error: 'Echo 初始化失敗' }); // 移除
            }
        });
    </script>
@endpush
