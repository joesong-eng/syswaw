{{-- <x-app-layout> --}}
@extends('layouts.app')
    @section('content')
    <style>
        #feedback-container {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }
    
        .feedback-message {
            padding: 5px;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
    </style>
    
    <x-slot name="header">
        <div class="flex items-center m-1 border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100 w-full">
            <div class="flex font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight justify-start w-[50%] text-left">
                {{ __('msg.data_monitoring') }}
            </div>
        </div>
    
        @if (session('success'))
        <div id="success-message" class="message mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded-lg transition-opacity duration-1000 ease-out transform">
            {{ session('success') }}
        </div>
        @endif
        @if ($errors->any())
        <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ __('msg.error') }}: {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </x-slot>
    <div class="container px-2 pt-5">
        <div class="flex w-full ms-4 p-0">
            <div id="message-container" class="flex font-semibold text-sm text-red-500 justify-start text-left w-[80%] ms-4 p-0">
                {{ 'msg.message' }}
            </div>
            <div class="flex text-sm text-green-700 dark:text-green-200 justify-end text-right w-[20%] mx-4 p-0">
                {{ __('msg.online') }}: <span id="online-machine-count">{{ \Illuminate\Support\Facades\Redis::get('online_clients')?:0 }}</span>
            </div>
        </div>
        <hr>
        <div id="loadingOverlay" class="loading-overlay">
            <div class="spinner"></div>
        </div>
    
        <div class="relative bg-white dark:bg-gray-800 max-w-2xl mx-auto overflow-hidden shadow-sm sm:rounded-lg">
            <div id="c-list" class="c-list py-1 px-0 mx-auto bg-gray-100 dark:bg-gray-800">
                <div class="flex justify-between mx-0 sm:mx-6 mb-2 text-gray-900 dark:text-gray-100 border-b border-gray-200 p-0">
                    <button id="start-tcp" onclick="tcpservice('start')" class="p-1 rounded-md bg-gray-500 text-gray-200">
                        {{ __('msg.start') }}
                    </button>
                    <button id="stop-tcp" onclick="tcpservice('stop')" class="p-1 rounded-md bg-gray-500 text-gray-200">
                        {{ __('msg.stop') }}
                    </button>
                    <button id="status-tcp" onclick="tcpservice('status')" class="p-1 rounded-md bg-yellow-300 dark:bg-yellow-500 text-gray-900 dark:text-gray-800">
                        {{ __('msg.status') }}
                    </button>
                    <button id="open-get" onclick="openGate()" class="p-1 rounded-md border border-black dark:border-gray-200 text-gray-900 dark:text-gray-100 mr-3">
                        {{ __('msg.retrieve_data') }}
                    </button>
                    <button name="el1" id="refreshButton" onclick="location.reload();" class="p-1 bg-blue-500 text-white rounded">
                        {{ __('msg.refresh') }}
                    </button>
                </div>
                <hr>
                <div class="mb-4">
                    <label for="machineIds" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Machine IDs (comma-separated):</label>
                    <input type="text" name="machineIds" id="machineIds" class="mt-1 p-2 border rounded-md w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
                <div id="content-list" name="content-list" class="pb-5 overflow-y-auto max-h-[calc(100vh-230px)] sm:max-h-[calc(100vh-180px)]">
                    </div>
            </div>
        </div>
    </div>
    
    <script>
        function tcpservice(action) {
            showLoadingOverlay();
            fetch('/admin/tcp-server/' + action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.message) {
                    fetchMessage();
                }
                console.log('success' + data);
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                hideLoadingOverlay();
            });
        }
    
        function openGate() {
            showLoadingOverlay();
            const machineIds = document.getElementById('machineIds').value;
            fetch('/admin/tcp-server/query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ machine_ids: machineIds.split(',') })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.message) {
                    fetchMessage();
                }
                console.log(data);
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                hideLoadingOverlay();
            });
        }
    
        function fetchMessage() {
            fetch('/admin/tcp-server/status')
                .then(response => response.json())
                .then(data => {
                    updateUI(data.status);
                    updateTransactionsUI();
                })
                .catch(error => console.error('Fetch error:', error));
        }
    
        // function fetchTransactions() {
        //     fetch('/admin/transactions') // 假設您有一個路由來獲取交易數據
        //         .then(response => response.json())
        //         .then(data => {
        //             updateTransactionsUI(data.transactions);
        //         })
        //         .catch(error => console.error('Fetch error:', error));
        // }
    
        function updateUI(status) {
            const container = document.getElementById('message-container');
            container.textContent = `Server Status: ${status}`;
            updateButtonStyles(status === 'running');
        }
    
        function updateTransactionsUI(transactions) {
            const transactionsContainer = document.getElementById('content-list');
            transactionsContainer.innerHTML = '';
            transactions.forEach(transaction => {
                let created_at = formatDateTime(transaction.created_at);
                const transactionElement = document.createElement('div');
                transactionElement.classList.add('flex', 'w-full', 'items-center', 'border-b', 'border-gray-600', 'dark:border-gray-300', 'text-sm', 'font-medium', 'text-gray-700', 'dark:text-gray-100', 'p-1');
                transactionElement.innerHTML = `
                    <div class="flex w-full">
                        <div class="w-[35%] px-1 sidr justify-start">${transaction.id}. ${transaction.token}</div>
                        <div class="w-[17%] px-1 sidr justify-center">${transaction.machine_id}</div>
                        <div class="w-[10%] px-1 sidr justify-center text-center">${number_format(transaction.credit_in, 0)}</div>
                        <div class="w-[10%] px-1 sidr justify-center text-center">${number_format(transaction.ball_in, 0)}</div>
                        <div class="w-[10%] px-1 sidr justify-center text-center">${number_format(transaction.ball_out, 0)}</div>
                        <div class="w-[18%] px-1 justify-center text-center">${created_at}</div>
                    </div>
                `;
                transactionsContainer.appendChild(transactionElement);
            });
        }
    
        function number_format(number, decimals) {
            return parseFloat(number).toFixed(decimals);
        }
    
        function formatDate(dateString) {
            const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Taipei' };
            return new Date(dateString).toLocaleString('zh-TW', options).replace(/\/|,/g, '-');
        }
    
        function Online() {
            fetch('/admin/onlineMachines')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('online-machine-count').innerText = data.count;
                })
                .catch(error => console.error('Error fetching online clients count:', error));
        }
    
        setInterval(() => {
            Online();
        }, 10000);
    
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            const year = date.getFullYear().toString().slice(-2);
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${year}${month}${day} ${hours}:${minutes}`;
        }
    
        function updateButtonStyles(isConnected) {
            const startButton = document.getElementById('start-tcp');
            const stopButton = document.getElementById('stop-tcp');
            if (isConnected) {
                startButton.style.backgroundColor = 'gray';
                startButton.style.color = 'darkgray';
                startButton.disabled = true;
                stopButton.style.backgroundColor = 'green';
                stopButton.style.color = 'white';
                stopButton.disabled = false;
            } else {
                startButton.style.backgroundColor = 'green';
                startButton.style.color = 'white';
                startButton.disabled = false;
                stopButton.style.backgroundColor = 'gray';
                stopButton.style.color = 'darkgray';
                stopButton.disabled = true;
            }
        }
    
        function showLoadingOverlay() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
    
        function hideLoadingOverlay() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    </script>
    @endsection
@php
    $title = '即時數據';
@endphp
{{-- </x-app-layout> --}}
