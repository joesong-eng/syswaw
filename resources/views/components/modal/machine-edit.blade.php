<div x-cloak class="fixed inset-0 z-50" x-show="editMachineModal" x-data="machineEdit"
    @keydown.escape="editMachineModal = false">
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="editMachineModal = false"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 bg-white w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.edit_machine') }}</h2>
            <form
                :action="`{{ Auth::user()->hasRole('admin') ? url('admin/machine/update') : url('machines/update') }}/${selectedMachine.id}`"
                method="POST">
                @csrf
                @method('PATCH')

                <!-- 名稱 -->
                <div class="mb-4">
                    <label for="name"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                    <input type="text" name="name" id="name"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-model="selectedMachine.name" required>
                </div>

                <!-- 機器種類 -->
                <div class="mb-4">
                    <x-modal.machine-type />
                </div>

                <!-- 營業廳 -->
                <div class="mb-4">
                    <label for="arcade_id"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.select_arcade') }}</label>
                    <select id="arcade_id" name="arcade_id"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-model="selectedMachine.arcade_id" required>
                        <option value="">{{ __('msg.select_arcade') }}</option>
                        @foreach ($arcades as $arcade)
                            <option value="{{ $arcade->id }}">{{ $arcade->name }}
                                ({{ $arcade->type === 'physical' ? __('msg.physical_arcade') : __('msg.virtual_arcade') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 芯片密鑰 -->
                <div class="mb-4">
                    <label for="chipkey" class="block text-sm font-medium text-gray-700">
                        {{ __('msg.machine_auth_key_display_label') }}
                        {{-- 修改：判斷和顯示 machineAuthKey.auth_key --}}
                        <span x-show="selectedMachine.machine_auth_key?.auth_key" class="text-xs text-green-600">
                            (當前: <span x-text="selectedMachine.machine_auth_key?.auth_key || 'N/A'"></span>)
                        </span>
                    </label>
                    <input type="text" name="chipkey" id="chipkey"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        {{-- 修改：動態綁定 placeholder --}}
                        x-bind:placeholder="selectedMachine.machine_auth_key?.auth_key ?
                            selectedMachine.machine_auth_key.auth_key :
                            '輸入可用的機器驗證金鑰'"
                        x-model="chipKeyInput" {{-- 修改：extractChipKey 函數可能仍適用，但檢查 :disabled 和 :class 的條件 --}} @paste.prevent="extractChipKey($event)"
                        :disabled="selectedMachine.machine_auth_key?.auth_key"
                        x-bind:class="{ 'bg-gray-200 cursor-not-allowed': selectedMachine.machine_auth_key?.auth_key }">
                    <p class="mt-1 text-sm text-gray-500">僅在機器未綁定金鑰時可輸入。可貼上金鑰或包含金鑰的 URL。</p>
                </div>
                {{-- 新增：晶片硬體 ID --}}
                <div class="mb-4">
                    <label for="edit_chip_hardware_id"
                        class="block text-sm font-medium text-gray-700">
                        {{ __('msg.chip_hardware_id') }}
                    </label>
                    <input type="text" name="chip_hardware_id" id="edit_chip_hardware_id" {{-- 修改：將 x-model 綁定到新的 Alpine 屬性 --}}
                        x-model="chipHardwareIdInput"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="{{ __('msg.chip_hardware_id_placeholder') }}">
                    <p class="mt-1 text-sm text-gray-500">如果綁定新金鑰，請在此輸入對應的硬體 ID。</p>
                </div>
                {{-- <!-- 分成比例 -->
                <div class="mb-4">
                    <label for="revenue_split"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.revenue_split') }}</label>
                    <input type="number" name="revenue_split" id="revenue_split" step="1" min="1"
                        max="0.95"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-model="selectedMachine.revenue_split" required>
                    <p class="mt-1 text-sm text-gray-500">{{ __('msg.revenue_split_help') }}</p>
                </div> --}}
                <!-- 擁有者（不可編輯） -->
                <div class="mb-4">
                    <label for="owner_id"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                    <span
                        class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-200 cursor-not-allowed p-2"
                        x-text="selectedMachine.owner?.name || 'N/A'"></span>
                    <input type="hidden" name="owner_id" x-model="selectedMachine.owner_id">
                </div>

                <!-- 取消按鈕 -->
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="editMachineModal = false"
                        class="px-4 py-2 bg-gray-300 rounded-md text-gray-700 hover:bg-gray-400">{{ __('msg.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">{{ __('msg.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('machineEdit', () => ({
            chipKeyInput: '', // 用於 Auth Key 輸入
            chipHardwareIdInput: '', // **** 新增：用於 Chip Hardware ID 輸入 ****
            init() {
                // 監聽從父組件分發的事件，以確保 selectedMachine 已更新
                this.$watch('selectedMachine', (newVal) => {
                    this.chipHardwareIdInput = newVal.machine_auth_key?.chip_hardware_id ||
                        ''; // **** 新增：初始化 chipHardwareIdInput ****
                });
                this.chipKeyInput = '';
                // 或者 this.selectedMachine.machine_auth_key?.auth_key || ''; 如果需要在啟用時預填
            },
            extractChipKey(event) {
                // *** 修改：檢查 machine_auth_key ***
                if (this.selectedMachine.machine_auth_key?.auth_key) return; // 已綁定時不處理

                const pastedText = (event.clipboardData || window.clipboardData).getData('text');
                // *** 修改：更新正則表達式以匹配新的 auth_key 或其可能的 URL 格式 (如果有的話) ***
                const regex = /([a-zA-Z0-9]{32,})/; // 示例：匹配32位以上的字母數字組合 (請根據實際 auth_key 格式調整)
                const match = pastedText.match(regex);
                this.chipKeyInput = match ? match[0] : pastedText; // 假設匹配到的就是 key 本身
            }
        }));
    });
</script>
