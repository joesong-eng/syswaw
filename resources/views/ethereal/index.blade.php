<!-- resources/views/ethereal/index.blade.php -->
@extends('layouts.app')
@section('content')
    <div class="my-5 p-2 shadow">
        <h1 class="text-xl py-3"><a class="text-xl text-emerald-500 font-extrabold">Reverb</a> 測試連線頁面</h1>
        <p id="Reverb_status" class="p-1">連線中...</p>
        <p id="Reverb_msg" class="p-1">尚無訊息</p>
        <div class="text-right pb-3">
            <x-secondary-button onclick="triggerEvent()">Trigger Evevt</x-secondary-button>
            <x-primary-button onclick="triggerBroadcast()" style="background-color: cadetblue;">Trigger
                Broadcast</x-primary-button>
        </div>
    </div>
    <div class="my-5 p-2 shadow">
        <p>
        <h1 class="text-xl py-3"><a class="text-xl text-indigo-500 font-extrabold">Redis</a> 測試連線頁面</h1>
        TCP Server 狀態：<div id="redis_status">
            @if ($status === 'running')
                redis_啟動中 ✅
            @elseif($status === 'stopped')
                redis_已停止 ❌
            @else
                目前狀態：未知
            @endif
        </div>

        </p>
        <div id="redis_msg" class="mt-2 text-sm text-blue-600"></div>
        <div class="text-right">
            {{-- <x-secondary-button onclick="triggerRedis()">Trigger Redis</x-secondary-button> --}}
            <button id="start-tcp" onclick="triggerRedis('start')" class="p-1 rounded-md bg-gray-500 text-gray-200">
                {{ __('msg.start') }}
            </button>
            <button id="stop-tcp" onclick="triggerRedis('stop')" class="p-1 rounded-md bg-gray-500 text-gray-200">
                {{ __('msg.stop') }}
            </button>
            <button id="status-tcp" onclick="triggerRedis('status')"
                class="p-1 rounded-md bg-yellow-300 text-gray-900">
                {{ __('msg.status') }}
            </button>
            <button id="restart-tcp" onclick="triggerRedis('restart')"
                class="p-1 rounded-md bg-yellow-300 text-gray-900">
                {{ __('msg.restart') }}
            </button>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                console.log('[Reverb] Echo 初始化完成');
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    document.getElementById('Reverb_status').innerText = '✅ 已連線 Reverb';
                });
            } else {
                console.error('❌ window.Echo 尚未初始化');
            }
        });


        window.triggerBroadcast = function() {
            const postData = {
                action: 'By Broadcast'
            };
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
            window.Echo.channel('reverb_tcpSvRespone')
                .listen('.tcpSvRespone.updated', (e) => {
                    console.log('✅ [指定事件] 收到: ', e);
                    document.getElementById('redis_status').innerText = xstate[e.status] || '未知狀態';
                    document.getElementById('redis_msg').innerText =
                        `TCP 狀態：${e.status}，動作：${e.action}`; // 修改顯示內容
                })
        })
        const actionFeedback = {
            start: '啟動中...',
            stop: '停止中...',
            restart: '重啟中...',
            status: '查詢狀態中...'
        };

        window.triggerRedis = function(status) {
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
    </script>
@endpush

@php
    $title = __('msg.dashboard.01');
@endphp
