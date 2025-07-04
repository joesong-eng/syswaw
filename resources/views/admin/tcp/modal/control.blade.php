<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold text-gray-800">TCP 伺服器控制

        <span id="redis_status"
            class="mt-2 inline-block px-3 py-0.5 rounded text-sm font-semibold bg-yellow-100 text-yellow-800">
            載入中...
        </span>
    </h3>
    <div class="mt-4 flex gap-2">
        <button id="start-tcp" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            onclick="triggerRedis('start')">啟動</button>
        <button id="stop-tcp" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            onclick="triggerRedis('stop')">停止</button>
        <button id="restart-tcp" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            onclick="triggerRedis('restart')">重新啟動</button>
    </div>
    <div id="controllOverlay" class="ctrl-overlay">
        <div class="spinner"></div>
    </div>
</div>
