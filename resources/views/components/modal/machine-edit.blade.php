<div x-cloak class="fixed inset-0 z-50" 
     x-show="editMachineModal" 
     x-data="machineEdit" 
     @keydown.escape="editMachineModal = false">
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="editMachineModal = false"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 bg-white dark:bg-gray-800 w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">{{ __('msg.edit_machine') }}</h2>
            <form :action="`{{ Auth::user()->hasRole('admin') ? url('admin/machine/update') : url('machines/update') }}/${selectedMachine.id}`" method="POST">
                @csrf
                @method('PATCH')

                <!-- 名稱 -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('msg.name') }}</label>
                    <input type="text" name="name" id="name" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" 
                           x-model="selectedMachine.name" required>
                </div>

                <!-- 機器種類 -->
                <div class="mb-4">
                    <label for="machine_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('msg.sort') }}</label>
                    <select name="machine_type" id="machine_type" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" 
                            x-model="selectedMachine.machine_type" required>
                        <option value="">{{ __('msg.select_type') }}</option>
                        <option value="type1">{{ __('msg.type1') }}</option>
                        <option value="type2">{{ __('msg.type2') }}</option>
                    </select>
                </div>

                <!-- 營業廳 -->
                <div class="mb-4">
                    <label for="arcade_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('msg.select_arcade') }}</label>
                    <select id="arcade_id" name="arcade_id" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" 
                            x-model="selectedMachine.arcade_id" required>
                        <option value="">{{ __('msg.select_arcade') }}</option>
                        @foreach ($arcades as $arcade)
                            <option value="{{ $arcade->id }}">{{ $arcade->name }} ({{ $arcade->type === 'physical' ? __('msg.physical_arcade') : __('msg.virtual_arcade') }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- 芯片密鑰 -->
                <div class="mb-4">
                    <label for="chipkey" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('msg.chip_token') }}
                        <span x-show="selectedMachine.chip?.key" class="text-xs text-green-600">
                            ({{ __('msg.current') }}: <span x-text="selectedMachine.chip?.key || 'N/A'"></span>)
                        </span>
                    </label>
                    <input type="text" name="chipkey" id="chipkey" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                           placeholder="{{ __('msg.enter_chip_key') }}"
                           x-model="chipKeyInput"
                           @paste.prevent="extractChipKey($event)"
                           :disabled="selectedMachine.chip?.key"
                           x-bind:class="{ 'bg-gray-200 cursor-not-allowed': selectedMachine.chip?.key }">
                    <p class="mt-1 text-sm text-gray-500">{{ __('msg.chip_key_help') }}</p>
                </div>

                <!-- 分成比例 -->
                <div class="mb-4">
                    <label for="revenue_split" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('msg.revenue_split') }}</label>
                    <input type="number" name="revenue_split" id="revenue_split" step="0.01" min="0.1" max="0.95"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" 
                           x-model="selectedMachine.revenue_split" required>
                    <p class="mt-1 text-sm text-gray-500">{{ __('msg.revenue_split_help') }}</p>
                </div>
                <!-- 擁有者（不可編輯） -->
                <div class="mb-4">
                    <label for="owner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('msg.owner') }}</label>
                    <span class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-200 cursor-not-allowed dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 p-2" 
                        x-text="selectedMachine.owner?.name || 'N/A'"></span>
                    <input type="hidden" name="owner_id" x-model="selectedMachine.owner_id">
                </div>

                <!-- 按鈕 -->
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
            chipKeyInput: '',
            init() {
                // 確保 selectedMachine.chip 是安全的
                this.chipKeyInput = this.selectedMachine.chip?.key || '';
            },
            extractChipKey(event) {
                if (this.selectedMachine.chip?.key) return; // 已綁定時不處理

                const pastedText = (event.clipboardData || window.clipboardData).getData('text');
                const regex = /https:\/\/sys\.tg25\.win\/chip\/([a-f0-9]{32})/i;
                const match = pastedText.match(regex);
                this.chipKeyInput = match ? match[1] : pastedText;
            }
        }));
    });
</script>