{{-- resources/views/admin/tcp/streamData.blade.php --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight w-full">
        🎧{{ __('即時數據') }}XX
    </h2>
@endsection
@section('content')
    <div class="flex flex-col justify-center bg-gray-100 w-full h-full px-1">
        <div class="w-full bg-white bg-opacity-80 rounded-lg p-1">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto max-h-[calc(100vh-100px)]">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col"
                                    class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">
                                    時間
                                </th>
                                <!-- 機器名稱與代號 -->
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[32%]"
                                    colspan="2">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">機器名稱</div>
                                        <div class="flex-1 text-center">代號</div>
                                    </div>
                                </th>
                                <!-- 進球/出球 -->
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">進球</div>
                                        <div class="flex-1 text-center">出球</div>
                                    </div>
                                </th>
                                <!-- 投幣/出獎 -->
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[14%]"
                                    colspan="2">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">投幣</div>
                                        <div class="flex-1 text-center">出獎</div>
                                    </div>
                                </th>
                                <!-- 開分/洗分 -->
                                <th scope="col"
                                    class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">
                                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                        <div class="flex-1 text-center">開分</div>
                                        <div class="flex-1 text-center">洗分</div>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="px-1 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[7%]">
                                    紙幣面額
                                </th>
                                <th scope="col"
                                    class="px-1 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[11%]">
                                    序號
                                </th>
                            </tr>
                        </thead>
                        <tbody id="stream-output-body" class="bg-white divide-y divide-gray-200">
                            <tr id="waiting-stream-row">
                                <td colspan="7" class="px-2 py-4 text-center text-sm text-gray-500">
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
@push('style')
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

        /* 移除所有 select 元素的默認下拉箭頭 */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
    </style>
@endpush
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const CONFIG = {
                MAX_RECORDS: 50,
                API_ENDPOINT: "{{ route('latestMqttData') }}", // 確保這裡指向新的只讀路由
                POLL_INTERVAL: 5000,
            };

            const streamTableBody = document.getElementById('stream-output-body');
            let lastData = [];

            function formatTimestamp(timestamp) {
                return timestamp ? new Date(timestamp).toLocaleString('zh-TW', {
                    year: '2-digit',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).replace(/\//g, '') : 'N/A';
            }

            function renderData(dataArray) {
                if (!dataArray || dataArray.length === 0) {
                    streamTableBody.innerHTML = `
                        <tr id="waiting-stream-row">
                            <td colspan="7" class="px-2 py-4 text-center text-sm text-gray-500">
                                等待數據流傳入...
                            </td>
                        </tr>`;
                    return;
                }

                streamTableBody.innerHTML = '';
                dataArray.forEach(item => {
                    const record = item.data && typeof item.data === 'object' ? item.data : {};
                    const row = document.createElement('tr');
                    const chipIdHash = record.chip_id ? record.chip_id.split('').reduce((acc, char) => acc +
                        char.charCodeAt(0), 0) : 0;
                    const bgColorClass = `bg-gray-${(chipIdHash % 4 + 1) * 100}`;

                    row.className = `${bgColorClass} bg-opacity-30`;
                    row.innerHTML = `
                        <td class="px-1 py-2 text-xs text-gray-700">${formatTimestamp(item.timestamp)}</td>
                        <td class="px-2 py-2 text-xs text-gray-700" colspan="2">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center truncate">${item.machine_name || 'N/A'}</div>
                                <div class="flex-1 text-center truncate">${record.chip_id || 'N/A'}</div>
                            </div>
                        </td>
                        <td class="px-2 py-2 text-xs text-center text-gray-700">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">${record.ball_in ?? 0}</div>
                                <div class="flex-1 text-center">${record.ball_out ?? 0}</div>
                            </div>
                        </td>
                        <td class="px-2 py-2 text-xs text-center text-gray-700" colspan="2">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">${record.credit_in ?? 0}</div>
                                <div class="flex-1 text-center">${record.coin_out ?? 0}</div>
                            </div>
                        </td>
                        <td class="px-2 py-2 text-xs text-center text-gray-700">
                            <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                                <div class="flex-1 text-center">${record.assign_credit ?? 0}</div>
                                <div class="flex-1 text-center">${record.settled_credit ?? 0}</div>
                            </div>
                        </td>
                        <td class="px-1 py-2 text-xs text-center text-gray-700">${record.bill_denomination ?? 0}</td>
                        <td class="px-1 py-2 text-xs text-gray-700 truncate" title="${item.auth_key_string || ''}">${item.auth_key_string || 'N/A'}</td>
                    `;
                    streamTableBody.appendChild(row);
                });
            }

            async function fetchData() {
                try {
                    const response = await fetch(CONFIG.API_ENDPOINT, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    });
                    if (!response.ok) {
                        console.error('API 請求失敗:', response.statusText);
                        return;
                    }
                    const newData = await response.json();
                    lastData = newData;
                    renderData(newData);
                } catch (error) {
                    console.error('獲取數據失敗:', error);
                }
            }

            fetchData();
            setInterval(fetchData, CONFIG.POLL_INTERVAL);
        });
    </script>
@endpush
