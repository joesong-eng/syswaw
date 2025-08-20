<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遊戲機監控儀表板</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .status-indicator {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .status-online {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .status-offline {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
        }

        .metric-value {
            font-variant-numeric: tabular-nums;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
        }

        .device-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, transparent 0%, rgba(255, 255, 255, 0.08) 50%, transparent 100%);
            transform: translateX(-100%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .pulse-dot {
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .collapse-transition {
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .device-card-collapsed {
            max-height: 80px;
        }

        .device-card-expanded {
            max-height: 1000px;
        }

        /* Custom gradient backgrounds */
        .bg-warm-gradient {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .bg-cool-gradient {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .bg-fresh-gradient {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .bg-soft-gradient {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        }
    </style>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-stone-50 via-gray-50 to-slate-100 dark:from-slate-900 dark:via-gray-800 dark:to-slate-800">
    <script>
        const applyTheme = (isDark) => {
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        };
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        applyTheme(mediaQuery.matches);
        mediaQuery.addEventListener('change', (e) => applyTheme(e.matches));
    </script>
    <div id="app" class="container mx-auto p-6 max-w-7xl">
        <!-- Professional Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-slate-600 to-gray-700 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold bg-gradient-to-r from-gray-700 to-slate-600 bg-clip-text text-transparent tracking-tight">
                            Gaming Machine Monitor</h1>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mt-1">
                            Real-time Device Status & Data Monitoring</p>
                    </div>
                </div>

                <div class="mt-4 lg:mt-0 flex items-center space-x-4">
                    <div
                        class="bg-white/80 backdrop-blur-sm dark:bg-gray-700/80 border border-white/50 dark:border-gray-600/50 rounded-xl px-4 py-3 flex items-center space-x-3 shadow-lg">
                        <div class="status-indicator w-2 h-2 rounded-full"
                            :class="reverbStatus.online ? 'status-online' : 'status-offline'">
                            <div v-if="reverbStatus.online" class="w-full h-full rounded-full bg-green-400 pulse-dot">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">@{{ reverbStatus.text }}</span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-white/70 backdrop-blur-sm dark:bg-gray-800/70 rounded-xl p-6 shadow-lg border border-white/50 dark:border-gray-700/50 hover:shadow-xl hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1">
                                Total Devices</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white metric-value">
                                @{{ Object.keys(devices).length }}</p>
                        </div>
                        <div
                            class="w-10 h-10 bg-slate-100 dark:bg-slate-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/70 backdrop-blur-sm dark:bg-gray-800/70 rounded-xl p-6 shadow-lg border border-white/50 dark:border-gray-700/50 hover:shadow-xl hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide mb-1">
                                Online Status</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white metric-value">
                                @{{ onlineDevicesCount }}</p>
                        </div>
                        <div
                            class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/70 backdrop-blur-sm dark:bg-gray-800/70 rounded-xl p-6 shadow-lg border border-white/50 dark:border-gray-700/50 hover:shadow-xl hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 uppercase tracking-wide mb-1">
                                Data Streams</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white metric-value">
                                @{{ activeDataStreams }}</p>
                        </div>
                        <div
                            class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/70 backdrop-blur-sm dark:bg-gray-800/70 rounded-xl p-6 shadow-lg border border-white/50 dark:border-gray-700/50 hover:shadow-xl hover:bg-white/80 dark:hover:bg-gray-800/80 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-1">
                                System Status</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white metric-value">
                                @{{ systemHealth }}%</p>
                        </div>
                        <div
                            class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Cards -->
        <div v-if="Object.keys(devices).length === 0" class="text-center py-20">
            <div class="max-w-md mx-auto">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">No Device Data Available</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Waiting for device connections...</p>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="(device, chipId) in devices" :key="chipId"
                class="bg-white/60 backdrop-blur-sm dark:bg-gray-800/60 rounded-2xl overflow-hidden shadow-xl border border-white/40 dark:border-gray-700/40 hover:shadow-2xl hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-500">

                <!-- Device Header - Always visible -->
                <div class="device-header bg-gradient-to-r from-slate-700 via-slate-600 to-gray-700 dark:from-slate-800 dark:via-slate-700 dark:to-gray-800 text-white p-4 relative overflow-hidden"
                    :class="!device.data || Object.keys(device.data).length === 0 ? 'cursor-pointer' : ''"
                    @click="!device.data || Object.keys(device.data).length === 0 ? toggleDeviceCard(chipId) : null">
                    <div class="relative z-10">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold mb-1 truncate">@{{ device.name || 'Unnamed Device' }}</h3>
                                <div class="text-xs space-y-1">
                                    <p class="text-white/80 font-mono">@{{ chipId }}</p>
                                    <p class="text-white/70">@{{ getDeviceType(device.name) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-3">
                                <div class="status-indicator w-2 h-2 rounded-full"
                                    :class="device.isOnline ? 'status-online' : 'status-offline'"></div>
                                <span name="mqttMCstatus" class="text-xs font-medium whitespace-nowrap"
                                    :class="device.isOnline ? 'text-green-200' : 'text-orange-200'">
                                    @{{ device.isOnline ? 'ONLINE' : 'OFFLINE' }}
                                </span>
                                <!-- Collapse indicator for devices without data -->
                                <svg v-if="!device.data || Object.keys(device.data).length === 0"
                                    class="w-4 h-4 text-white/70 transition-transform duration-300"
                                    :class="{ 'rotate-180': !isDeviceCollapsed(chipId) }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device Data - Collapsible for devices without data -->
                <div class="collapse-transition"
                    :class="!device.data || Object.keys(device.data).length === 0 ?
                        (isDeviceCollapsed(chipId) ? 'device-card-collapsed' : 'device-card-expanded') :
                        'device-card-expanded'">

                    <div v-if="device.data && Object.keys(device.data).length > 0" class="p-6">
                        <!-- Gaming Machine Data Grid -->
                        <div v-if="isGamingMachine(device.data)" class="space-y-4">
                            <div>
                                <h4
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1">
                                    Machine Metrics</h4>
                                <p class="text-xs font-mono text-gray-500 dark:text-gray-400 mb-1">
                                    Device Key: @{{ chipId }}</p>
                                <p v-if="device.data && device.data.chip_id"
                                    class="text-xs font-mono text-gray-500 dark:text-gray-400 mb-3">
                                    Data Chip ID: @{{ device.data.chip_id }}</p>
                                <div class="data-grid">
                                    <!-- 投幣 -->
                                    <div class="bg-gradient-to-br from-indigo-50 to-slate-50 dark:from-indigo-900/20 dark:to-slate-800/20 border border-indigo-200/60 dark:border-indigo-600/30 rounded-xl p-4 text-center hover:from-indigo-100 hover:to-slate-100 dark:hover:from-indigo-800/30 dark:hover:to-slate-700/30 transition-all duration-300"
                                        v-if="device.data.credit_in !== undefined">
                                        <div
                                            class="text-lg font-bold text-indigo-700 dark:text-indigo-300 metric-value">
                                            @{{ device.data.credit_in || 0 }}</div>
                                        <div
                                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mt-1 uppercase tracking-wide">
                                            投幣</div>
                                    </div>

                                    <!-- 退幣 -->
                                    <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-800/20 border border-emerald-200/60 dark:border-emerald-600/30 rounded-xl p-4 text-center hover:from-emerald-100 hover:to-green-100 dark:hover:from-emerald-800/30 dark:hover:to-green-700/30 transition-all duration-300"
                                        v-if="device.data.coin_out !== undefined">
                                        <div
                                            class="text-lg font-bold text-emerald-700 dark:text-emerald-300 metric-value">
                                            @{{ device.data.coin_out || 0 }}</div>
                                        <div
                                            class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1 uppercase tracking-wide">
                                            退幣</div>
                                    </div>

                                    <!-- 球入 -->
                                    <div class="bg-gradient-to-br from-blue-50 to-slate-50 dark:from-blue-900/20 dark:to-slate-800/20 border border-blue-200/60 dark:border-blue-600/30 rounded-xl p-4 text-center hover:from-blue-100 hover:to-slate-100 dark:hover:from-blue-800/30 dark:hover:to-slate-700/30 transition-all duration-300"
                                        v-if="device.data.ball_in !== undefined">
                                        <div class="text-lg font-bold text-blue-700 dark:text-blue-300 metric-value">
                                            @{{ device.data.ball_in || 0 }}</div>
                                        <div
                                            class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-1 uppercase tracking-wide">
                                            球入</div>
                                    </div>

                                    <!-- 球出 -->
                                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-800/20 border border-amber-200/60 dark:border-amber-600/30 rounded-xl p-4 text-center hover:from-amber-100 hover:to-yellow-100 dark:hover:from-amber-800/30 dark:hover:to-yellow-700/30 transition-all duration-300"
                                        v-if="device.data.ball_out !== undefined">
                                        <div class="text-lg font-bold text-amber-700 dark:text-amber-300 metric-value">
                                            @{{ device.data.ball_out || 0 }}</div>
                                        <div
                                            class="text-xs font-semibold text-amber-600 dark:text-amber-400 mt-1 uppercase tracking-wide">
                                            球出</div>
                                    </div>

                                    <!-- 開分 -->
                                    <div class="bg-gradient-to-br from-blue-50 to-slate-50 dark:from-blue-900/20 dark:to-slate-800/20 border border-blue-200/60 dark:border-blue-600/30 rounded-xl p-4 text-center hover:from-blue-100 hover:to-slate-100 dark:hover:from-blue-800/30 dark:hover:to-slate-700/30 transition-all duration-300"
                                        v-if="device.data.assign_credit !== undefined">
                                        <div class="text-lg font-bold text-blue-700 dark:text-blue-300 metric-value">
                                            @{{ device.data.assign_credit || 0 }}</div>
                                        <div
                                            class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-1 uppercase tracking-wide">
                                            開分</div>
                                    </div>

                                    <!-- 洗分 -->
                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-800/20 border border-green-200/60 dark:border-green-600/30 rounded-xl p-4 text-center hover:from-green-100 hover:to-emerald-100 dark:hover:from-green-800/30 dark:hover:to-emerald-700/30 transition-all duration-300"
                                        v-if="device.data.settled_credit !== undefined">
                                        <div class="text-lg font-bold text-green-700 dark:text-green-300 metric-value">
                                            @{{ device.data.settled_credit || 0 }}</div>
                                        <div
                                            class="text-xs font-semibold text-green-600 dark:text-green-400 mt-1 uppercase tracking-wide">
                                            洗分</div>
                                    </div>

                                    <!-- 紙鈔投入 -->
                                    <div class="bg-gradient-to-br from-stone-50 to-gray-50 dark:from-stone-900/20 dark:to-gray-800/20 border border-stone-200/60 dark:border-stone-600/30 rounded-xl p-4 text-center hover:from-stone-100 hover:to-gray-100 dark:hover:from-stone-800/30 dark:hover:to-gray-700/30 transition-all duration-300"
                                        v-if="device.data.bill_denomination !== undefined">
                                        <div class="text-lg font-bold text-stone-700 dark:text-stone-300 metric-value">
                                            @{{ formatNumber(device.data.bill_denomination) }}</div>
                                        <div
                                            class="text-xs font-semibold text-stone-600 dark:text-stone-400 mt-1 uppercase tracking-wide">
                                            紙鈔投入</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Raw JSON Data for Non-Gaming Devices -->
                        <div v-else>
                            <h4
                                class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-cyan-500 dark:text-cyan-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                Raw Data Stream
                            </h4>
                            <div
                                class="bg-gradient-to-br from-slate-800 to-slate-900 dark:from-gray-900 dark:to-black border border-slate-600/50 dark:border-gray-700/50 rounded-xl p-4 shadow-inner">
                                <pre class="text-xs text-emerald-300 dark:text-green-400 overflow-x-auto whitespace-pre-wrap break-words">@{{ formatJSON(device.data) }}</pre>
                            </div>
                        </div>

                        <!-- Timestamp -->
                        <hr class="border-gray-200/60 dark:border-gray-600/40 mt-6 mb-4">
                        <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">Last
                                Update</span>
                            <span name="e-time"
                                class="font-medium text-gray-600 dark:text-gray-300">@{{ formatTimestamp(device.lastUpdate) }}</span>
                        </div>
                    </div>

                    <!-- No Data State - Collapsible -->
                    <div v-else class="p-4">
                        <div class="text-center">
                            <div
                                class="w-8 h-8 bg-gradient-to-br from-slate-100 to-gray-100 dark:from-slate-900/30 dark:to-gray-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Waiting for data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const {
            createApp
        } = Vue

        createApp({
            data() {
                const initialMachines = {!! json_encode($machines ?? []) !!};
                const devices = {};

                initialMachines.forEach(machine => {
                    const chipID = machine.machine_auth_key.chip_hardware_id;
                    if (chipID) {
                        devices[chipID] = {
                            ...machine,
                            isOnline: machine.isOnline || false, // 使用後端傳遞的 isOnline 狀態，如果沒有則預設為 false
                            data: null,
                            lastUpdate: null
                        };
                    }
                });

                return {
                    devices: devices,
                    collapsedDevices: new Set(), // Track collapsed devices
                    reverbStatus: {
                        online: false,
                        text: 'Initializing...'
                    }
                }
            },
            computed: {
                onlineDevicesCount() {
                    return Object.values(this.devices).filter(device => device.isOnline).length;
                },
                activeDataStreams() {
                    return Object.values(this.devices).filter(device => device.data && Object.keys(device.data)
                        .length > 0).length;
                },
                systemHealth() {
                    const total = Object.keys(this.devices).length;
                    if (total === 0) return 100;
                    return Math.round((this.onlineDevicesCount / total) * 100);
                }
            },
            mounted() {
                this.initializeEcho();

                // Initialize collapsed state for devices without data
                Object.keys(this.devices).forEach(chipId => {
                    const device = this.devices[chipId];
                    if (!device.data || Object.keys(device.data).length === 0) {
                        this.collapsedDevices.add(chipId);
                    }
                });
            },
            methods: {
                toggleDeviceCard(chipId) {
                    if (this.collapsedDevices.has(chipId)) {
                        this.collapsedDevices.delete(chipId);
                    } else {
                        this.collapsedDevices.add(chipId);
                    }
                    // Force reactivity update
                    this.collapsedDevices = new Set(this.collapsedDevices);
                },

                isDeviceCollapsed(chipId) {
                    return this.collapsedDevices.has(chipId);
                },

                initializeEcho() {
                    window.Echo = new Echo({
                        broadcaster: 'reverb',
                        key: '{{ $reverb_app_key }}',
                        wsHost: '{{ $reverb_host }}',
                        wsPort: '{{ $reverb_port }}',
                        wssPort: '{{ $reverb_port }}',
                        forceTLS: '{{ $reverb_scheme }}' === 'wss',
                        enabledTransports: ['ws', 'wss'],
                    });

                    this.listenForConnectionStatus();
                    this.listenForMachineData();
                    this.listenForMachineStatus(); // 新增：監聽機器狀態更新
                },

                listenForMachineStatus() {
                    window.Echo.channel('machine-status')
                        .listen('.machine.status.updated', (event) => {
                            console.log('Machine Status Event Received:', event); // 新增日誌
                            if (event.chip_id) {
                                const chipID = event.chip_id;
                                if (this.devices[chipID]) {
                                    const newStatus = event.status === 'online';
                                    this.devices[chipID] = {
                                        ...this.devices[chipID],
                                        isOnline: newStatus,
                                        data: newStatus ? this.devices[chipID].data : null, // 如果離線，清除數據
                                        lastUpdate: new Date()
                                    };
                                }
                            }
                        });
                },

                listenForConnectionStatus() {
                    const connection = window.Echo.connector.pusher.connection;
                    connection.bind('connecting', () => {
                        this.reverbStatus = {
                            online: false,
                            text: 'Connecting...'
                        };
                    });
                    connection.bind('connected', () => {
                        this.reverbStatus = {
                            online: true,
                            text: 'Connected'
                        };
                    });
                    connection.bind('unavailable', () => {
                        this.reverbStatus = {
                            online: false,
                            text: 'Service Unavailable'
                        };
                    });
                    connection.bind('failed', () => {
                        this.reverbStatus = {
                            online: false,
                            text: 'Connection Failed'
                        };
                    });
                    connection.bind('disconnected', () => {
                        this.reverbStatus = {
                            online: false,
                            text: 'Disconnected'
                        };
                    });
                },

                listenForMachineData() {
                    window.Echo.channel('machine-data')
                        .listen('.machine.data.received', (event) => {
                            console.log('Machine Data Event Received:', event);
                            if (event.data && event.data.chip_id) {
                                const chipID = event.data.chip_id;
                                console.log(`Processing data for chipID: ${chipID}`);
                                if (this.devices[chipID]) {
                                    console.log(`Updating data for existing device: ${chipID}`);
                                    this.devices[chipID].data = event.data;
                                    this.devices[chipID].lastUpdate = new Date();
                                    this.collapsedDevices.delete(chipID);
                                    this.collapsedDevices = new Set(this.collapsedDevices);
                                } else {
                                    console.warn(
                                        `Received data for unknown or uninitialized device: ${chipID}. Current devices:`,
                                        Object.keys(this.devices));
                                }
                            } else {
                                console.warn('Received machine data event without chip_id:', event);
                            }
                        });
                },

                getDeviceType(name) {
                    if (!name) return 'Unknown Device';
                    const lowerName = name.toLowerCase();
                    if (lowerName.includes('lottery')) return 'Lottery Machine';
                    if (lowerName.includes('bill')) return 'Bill Acceptor';
                    if (lowerName.includes('gambling')) return 'Gaming Machine';
                    if (lowerName.includes('esplot')) return 'Electronic Game';
                    return 'Entertainment Device';
                },

                isGamingMachine(data) {
                    return data && (
                        data.coin_in !== undefined ||
                        data.coin_out !== undefined ||
                        data.ball_in !== undefined ||
                        data.ball_out !== undefined ||
                        data.credit_in !== undefined ||
                        data.credit_out !== undefined
                    );
                },

                formatNumber(num) {
                    if (num === undefined || num === null) return '0';
                    return Number(num).toLocaleString();
                },

                formatJSON(obj) {
                    return JSON.stringify(obj, null, 2);
                },

                formatTimestamp(timestamp) {
                    if (!timestamp) return 'Unknown';

                    let date;
                    if (typeof timestamp === 'number') {
                        date = new Date(timestamp); // 假設傳入的數字是毫秒級 timestamp
                    } else if (timestamp instanceof Date) {
                        date = timestamp;
                    } else {
                        date = new Date(timestamp);
                    }

                    if (isNaN(date.getTime())) return 'Invalid Date'; // 檢查是否為有效日期

                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;

                    const formattedTime = date.toLocaleTimeString('zh-TW', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    return formattedDate + ' ' + formattedTime;
                }
            }
        }).mount('#app')
    </script>
</body>

</html>
