@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('歷史記錄') }}
    </h2>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">
        <!-- 按鈕 -->
        <div id="captureOverlay" class="ctrl-overlay">
            <div class="spinner"></div>
        </div>
        <div class="flex justify-end">
            <button id="capture-data-btn" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                {{ __('msg.current_data') }}
            </button>
        </div>
        <form id="filterForm">
            <!-- 表格容器 -->
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
                <div class="table-container max-h-[calc(100vh-16rem)] overflow-y-auto">
                    <table class="w-full table-fixed border-collapse">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800 z-10">ID</th>
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800 z-10">
                                    <select name="arcade_id" id="arcade_id"
                                        onchange="showLoadingOverlay(); document.getElementById('filterForm').submit();"
                                        class="w-full bg-transparent text-center cursor-pointer hover:text-blue-500 dark:hover:text-blue-400 text-sm">
                                        <option value="all" {{ (string) $filterArcadeId === 'all' ? 'selected' : '' }}>店家
                                        </option>
                                        @foreach ($arcades as $arcade)
                                            <option value="{{ $arcade->id }}"
                                                {{ (string) $filterArcadeId === (string) $arcade->id ? 'selected' : '' }}>
                                                {{ $arcade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800 z-10">
                                    <select name="machine_id" id="machine_id"
                                        onchange="showLoadingOverlay(); document.getElementById('filterForm').submit();"
                                        class="w-full bg-transparent text-center cursor-pointer text-sm">
                                        <option value="all">{{ __('msg.all') }}</option>
                                    </select>
                                </th>
                                <th
                                    class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800  whitespace-pre-line ">
                                    {{ __('msg.ball_in') }}{{ __('msg.ball_out') }}</th>
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800">
                                    {{ __('msg.coin') }}</th>
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800">
                                    {{ __('msg.coin_out') }}</th>
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800 z-10">
                                    {{ __('msg.bill_denomination') }}</th>
                                <th
                                    class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800 z-10 whitespace-pre-line">
                                    {{ __('msg.assign_credit') }} {{ __('msg.settled_credit') }}</th>
                                <th class="px-2 py-3 text-center sticky top-0 bg-gray-100 dark:bg-gray-800 z-10">
                                    <select name="time_filter" id="time_filter"
                                        onchange="showLoadingOverlay(); document.getElementById('filterForm').submit();"
                                        class="w-full bg-transparent text-center cursor-pointer hover:text-blue-500 dark:hover:text-blue-400 text-sm">
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
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($records as $record)
                                <tr
                                    class="text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-2 py-2 text-center">
                                        {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-2 py-2 text-center truncate">
                                        {{ $record->arcade->name ?? '未知店铺' }}
                                    </td>
                                    <td class="px-2 py-2 text-center truncate">
                                        {{ $record->machine->name ?? '未知机器' }}
                                    </td>
                                    <td class="px-2 py-2 text-center whitespace-pre-line">{{ $record->ball_in }}
                                        {{ $record->ball_out }}</td>
                                    <td class="px-2 py-2 text-center">{{ $record->credit_in }}</td>
                                    <td class="px-2 py-2 text-center">{{ $record->coin_out }}</td>
                                    <td class="px-2 py-2 text-center">{{ $record->bill_denomination }}</td>
                                    <td class="px-2 py-2 text-center whitespace-pre-line">{{ $record->assign_credit }}
                                        {{ $record->settled_credit }}</td>
                                    <td class="px-2 py-2 text-center text-xs">
                                        {{ $record->timestamp ? \Carbon\Carbon::parse($record->timestamp)->format('ymd Hi:s') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                        暂无历史数据记录。
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
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.2);
            z-index: 50;
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
            window.showLoadingOverlay = (overlayId = 'captureOverlay') => {
                const overlay = document.getElementById(overlayId);
                if (overlay) overlay.style.display = 'flex';
            };
            const hideLoadingOverlay = (overlayId = 'captureOverlay') => {
                const overlay = document.getElementById(overlayId);
                if (overlay) overlay.style.display = 'none';
            };

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
                            'inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                    } else if (status === 'stopped') {
                        redisStatusDiv.className =
                            'inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                    } else {
                        redisStatusDiv.className =
                            'inline-block px-2 py-0.5 rounded text-xs sm:text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
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
                if (captureDataBtn) captureDataBtn.disabled = true;
                showLoadingOverlay('captureOverlay');
                fetch('/admin/tcp-server/openDataGate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.ok ? response.json() : response.json().then(err => Promise.reject(
                        err)))
                    .then(data => {
                        console.log('Capture Trigger Success:', data);
                        Swal.fire('成功', '數據截取成功，頁面即將刷新', 'success');
                        setTimeout(() => {
                            hideLoadingOverlay('captureOverlay');
                            location.reload();
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('Capture Trigger Error:', error);
                        Swal.fire('錯誤', `截取失敗: ${error.error || '請檢查日誌'}`, 'error');
                        hideLoadingOverlay('captureOverlay');
                    })
                    .finally(() => {
                        if (captureDataBtn) captureDataBtn.disabled = false;
                    });
            }

            if (captureDataBtn) {
                captureDataBtn.addEventListener('click', sendCaptureTrigger);
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
