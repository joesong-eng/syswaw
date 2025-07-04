{{-- resources/views/admin/tcp/modal/capture.blade.php --}}
@props(['status']) {{-- scheduleStatus prop 已不再需要 --}}
<div class="w-full py-4 px-4 bg-white rounded-lg shadow h-full">
    <div id="ctrl-card-btn-capture" class="ctrl-card-btn top-1 right-4 text-sm cursor-pointer">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">
            手動數據擷取
            {{-- 定時擷取狀態顯示已移除 --}}
        </h3>
    </div>
    <div id="card-ctrl-capture" class="card-ctrl space-y-4 pt-5">
        <div class="flex items-center gap-1 flex-wrap justify-center">
            <button id="capture-data-btn" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                {{ __('截取') }}
            </button>
        </div>
        <div id="captureOverlay" class="ctrl-overlay">
            <div class="spinner"></div>
        </div>
    </div>
</div>