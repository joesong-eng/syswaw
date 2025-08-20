<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQTT 設備監控儀表板</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js"></script>

    <style>
        body {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        pre {
            background-color: #1f2937;
            color: #d1d5db;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <div id="app" class="container mx-auto p-4 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-3xl font-bold mb-4 md:mb-0">MQTT 設備監控儀表板</h1>
            <div class="flex items-center space-x-2">
                <span class="text-lg">Reverb 連線狀態:</span>
                <span id="global-reverb-status"
                    class="px-3 py-1 text-sm font-semibold text-white rounded-full transition-colors duration-300"
                    :class="statusClass">
                    @{{ statusText }}
                </span>
            </div>
        </div>

        <div v-if="Object.keys(onlineDevices).length === 0" class="col-span-full text-center text-lg text-gray-500">
            正在等待接收設備資料...
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="(data, chipId) in onlineDevices" :key="chipId"
                class="m-card bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">設備 ID: @{{ chipId }}</h2>
                <pre><code class="text-sm">@{{ JSON.stringify(data, null, 2) }}</code></pre>
            </div>
        </div>
    </div>

    <script>
        const {
            createApp
        } = Vue

        createApp({
            data() {
                return {
                    onlineDevices: {},
                    statusText: '初始化中...',
                    statusClass: 'bg-gray-500',
                }
            },
            mounted() {
                this.initializeEcho();
            },
            methods: {
                initializeEcho() {
                    // 初始化 Laravel Echo
                    window.Echo = new Echo({
                        broadcaster: 'reverb',
                        key: '{{ $reverb_app_key }}',
                        wsHost: '{{ $reverb_host }}',
                        wsPort: '{{ $reverb_port }}',
                        wssPort: '{{ $reverb_port }}',
                        forceTLS: '{{ $reverb_scheme }}' === 'wss',
                        enabledTransports: ['ws', 'wss'],
                    });

                    console.log('Echo initialized');

                    // 監聽 Reverb 連線狀態
                    this.listenForConnectionStatus();

                    // 監聽機器資料頻道
                    this.listenForMachineData();
                },
                listenForConnectionStatus() {
                    const connection = window.Echo.connector.pusher.connection;
                    connection.bind('connecting', () => {
                        this.statusText = '連接中...';
                        this.statusClass = 'bg-yellow-500';
                    });
                    connection.bind('connected', () => {
                        this.statusText = '已連接 ✅';
                        this.statusClass = 'bg-green-500';
                    });
                    connection.bind('unavailable', () => {
                        this.statusText = '服務不可用 ⚠️';
                        this.statusClass = 'bg-red-500';
                    });
                    connection.bind('failed', () => {
                        this.statusText = '連接失敗 ❌';
                        this.statusClass = 'bg-red-500';
                    });
                    connection.bind('disconnected', () => {
                        this.statusText = '已斷開連接 🔌';
                        this.statusClass = 'bg-gray-500';
                    });
                },
                listenForMachineData() {
                    window.Echo.channel('machine-data')
                        .listen('.machine.data.received', (event) => {
                            console.log('Received broadcasted data:', event);
                            if (event.data && event.data.chip_id) {
                                const chip_id = event.data.chip_id;
                                // 使用 Vue 的方式更新資料，確保響應式
                                this.onlineDevices[chip_id] = event.data;
                            }
                        });

                    console.log("Listening on 'machine-data' channel...");
                }
            }
        }).mount('#app')
    </script>

</body>

</html>