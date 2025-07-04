                <!-- {{-- **** 新增：定時擷取控制 *********************************************** --}} -->
                <div x-cloak x-data="{ showTcpControl: false }" class="space-x-2 m-auto max-w-xl">
                    <div class="mt-4 p-4 bg-indigo-50 rounded-lg shadow">
                        <h3 class="text-md font-semibold mb-3 text-gray-800">定時擷取控制</h3>
                        <div class="flex flex-wrap items-center gap-4">
                            <div>
                                <label for="schedule_interval"
                                    class="text-sm text-gray-700 mr-2">擷取週期:</label>
                                <select id="schedule_interval" name="schedule_interval"
                                    class="border-gray-300 rounded-md shadow-sm">
                                    <option value="1">每 1 小時</option>
                                    <option value="2">每 2 小時</option>
                                    <option value="6" selected>每 6 小時</option> {{-- 預設選中 6 小時 --}}
                                    <option value="12">每 12 小時</option>
                                    <option value="24">每 24 小時</option>
                                </select>
                            </div>
                            <button id="start-schedule" onclick="triggerSchedule('start_schedule')"
                                class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-1 px-3 rounded text-sm disabled:opacity-50">啟動定時</button>
                            <button id="stop-schedule" onclick="triggerSchedule('stop_schedule')"
                                class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-1 px-3 rounded text-sm disabled:opacity-50">停止定時</button>
                        </div>
                        <!-- dd -->
                        <div class="mt-2 text-sm">
                            <span class="text-gray-700">目前狀態:</span>
                            <span id="schedule_status_display"
                                class="ml-1 font-medium text-blue-600">讀取中...</span>
                        </div>
                    </div>

                </div>
