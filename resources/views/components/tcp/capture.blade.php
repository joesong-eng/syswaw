{{-- resources/views/components/tcp/capture.blade.php --}}
@props(['status', 'scheduleStatus']) {{-- 接收 status 和 scheduleStatus --}}

<div class="relative w-full py-4 px-1 bg-indigo-100 rounded-lg shadow h-full"> {{-- 移除 mt-4, 添加 h-full 使高度一致 --}}
    {{-- Slightly larger heading, more margin --}}
    {{-- 主要內容區域 --}}
    <div id="card-ctrl-capture" class="card-ctrl space-y-4 pt-5 hidden"> {{-- 使用 space-y 提供垂直間距, 添加 ID --}}
        <!-- 操作按鈕行 -->
        <div class="flex items-center gap-1 flex-wrap justify-center"> {{-- 包裹按鈕，允許換行 --}}
            <!-- 週期選擇行 -->
            <div class="flex items-center gap-1 flex-wrap text-gray-700"> {{-- 包裹標籤、下拉選單和文字，允許換行 --}}
                <label for="schedule_interval" class="text-sm font-medium ">每</label>
                <select id="schedule_interval" name="schedule_interval" class="max-w-sm bg-gray-200 "
                    class="block w-full sm:w-auto border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="6" selected>6</option>
                    <option value="12">12</option>
                    <option value="24">24</option>
                </select> <a> 小時</a>
            </div>
            <button id="start-schedule" onclick="triggerSchedule('start_schedule')"
                class="inline-flex items-center justify-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 disabled:opacity-50 transition ease-in-out duration-150">
                啟動
            </button>
            <button id="stop-schedule" onclick="triggerSchedule('stop_schedule')"
                class="inline-flex items-center justify-center px-4 py-2 bg-orange-500 hover:bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 transition ease-in-out duration-150">
                停止
            </button>
            <button id="capture-data-btn"
                class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 transition ease-in-out duration-150">
                {{ __('截取') }}
            </button>
        </div>
    </div>
    <!-- 狀態顯示 (絕對定位) -->
    <div id="ctrl-card-btn-capture" class="ctrl-card-btn absolute top-1 right-4 text-sm cursor-pointer">
        {{-- 調整 top/right 邊距, 添加 ID 和 cursor-pointer --}}
        <span class="text-lg font-semibold mb-4 text-gray-800">定時擷取</span>
        <span id="schedule_status_display"
            class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">讀取中...</span>
    </div>
    <div id="captureOverlay" class="ctrl-overlay">
        <div class="spinner"></div>
    </div>
</div>
