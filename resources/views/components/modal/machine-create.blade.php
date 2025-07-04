<div x-cloak class="fixed inset-0 z-50" x-show="addMachineModal">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-1">
        machine_auth_keys
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
            @click.away="addMachineModal = false">
            <div class="bg-white p-3 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">
                    {{ __('msg.add') }}{{ __('msg.machine') }}</h2>
                <form action="{{ route('admin.machines.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="auth_key"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.chip_token') }}</label>
                        {{-- 修改：移除內層 x-data，狀態由 createMachineModal 管理 --}}
                        <div class="relative" x-data="createMachineModal"> {{-- 或者如果 x-data 在更外層，這裡就不需要 --}}
                            <input type="text" name="auth_key" id="auth_key" x-model="auth_key"
                                @paste=" setTimeout(() => {
                                  const pastedText = $event.target.value;
                                  const regex = /(?:https?:\/\/[^\/]+\/(?:chip|key)\/)?([a-zA-Z0-9]{8,})/i;
                                  const match = pastedText.match(/https?:\/\/[^\/]+\/chip\/([a-f0-9]+)/i);
                                  if (match && match[1]) { auth_key = match[1];}
                                }, 10)"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('msg.paste_chip_url_or_id') }}
                            </p>
                            {{-- 新增：生成金鑰按鈕 --}}
                            {{-- @if (Auth::user()->hasRole('admin')) --}}
                            <button type="button" @click="generateAndFillKey()" :disabled="isLoading"
                                class="absolute top-0 right-0 mt-1 mr-1 px-3 py-2 text-xs font-medium text-white bg-green-500 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span x-show="!isLoading">生成</span>
                                <span x-show="isLoading">生成中...</span>
                            </button>
                            {{-- @endif --}}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="name"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                        <input type="text" name="name" id="name"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                    </div>
                    <div class="mb-4">
                        <x-modal.machine-type />
                    </div>
                    <div class="mb-4">
                        <x-label for="arcade_id"
                            class="text-gray-700">{{ __('msg.select_arcade') }}:</x-label>
                        <select id="arcade_id" name="arcade_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="">{{ __('msg.select_arcade') }}</option>
                            @foreach ($arcades as $arcade)
                                <option value="{{ $arcade->id }}">{{ $arcade->id }}{{ $arcade->name }}
                                    ({{ $arcade->type === 'physical' ? __('msg.physical_arcade') : __('msg.virtual_arcade') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="create_chip_hardware_id"
                            class="block text-sm font-medium text-gray-700">
                            晶片硬體 ID (Chip Hardware ID) <span class="text-red-500">*</span> {{-- 標示為必填 --}}
                        </label>
                        <input type="text" name="chip_hardware_id" id="create_chip_hardware_id"
                            value="{{ old('chip_hardware_id') }}" required {{-- 添加 required --}}
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="輸入 ESP32 晶片的物理 ID">
                        <p class="mt-1 text-sm text-gray-500">請輸入與上方金鑰對應的物理晶片 ID。</p>
                    </div>
                    <!-- 分成比例選單 -->
                    {{-- <div class="mb-4">
                        <label for="revenue_split" class="block text-sm text-gray-600">分成比例</label>
                        <select id="revenue_split" name="revenue_split" class="w-full mt-1 p-2 border rounded-md">
                            @for ($i = 0.1; $i <= 0.95; $i += 0.05)
                                <option value="{{ number_format($i, 2) }}"
                                    @if (number_format($i, 2) == '0.4') selected @endif>
                                    {{ number_format($i, 2) }}
                                </option>
                            @endfor
                        </select>
                    </div> --}}
                    @if (Auth::user()->hasRole('admin'))
                        <div class="mb-4">
                            <label for="owner_id"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                            <select name="owner_id" id="owner_id"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                                <option id="default-option" value="" class="text-gray-500 text-sm font-thin">
                                    {{ __('msg.select') }} {{ __('msg.machine_owner') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="owner_id" id="owner_id" value={{ Auth::user()->id }}>
                    @endif
                    <div class="flex justify-end">
                        <button type="button" @click="addMachineModal = false"
                            class="mr-2 px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-md">{{ __('msg.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">{{ __('msg.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 新增：Alpine.js 函數定義 (可以放在 @push('scripts') 或頁面底部) --}}
@push('scripts')
    <script>
        // 確保在 Alpine 初始化後定義函數
        document.addEventListener('alpine:init', () => {
            // 假設 x-data 在父組件或這裡定義
            Alpine.data('createMachineModal', () => ({
                auth_key: '',
                isLoading: false,
                async generateAndFillKey() {
                    this.isLoading = true;
                    try {
                        const response = await fetch(
                            "{{ route('admin.machine_auth_keys.XXX') }}", { // 使用新的路由名稱
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                }
                            });
                        const data = await response.json();
                        if (response.ok && data.auth_key) {
                            this.auth_key = data.auth_key; // 將返回的金鑰填入 input
                        } else {
                            alert('生成金鑰失敗: ' + (data.message || '未知錯誤'));
                        }
                    } catch (error) {
                        console.error('生成金鑰請求錯誤:', error);
                        alert('生成金鑰請求失敗: ' + error.message);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>
@endpush
