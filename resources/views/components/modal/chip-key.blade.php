<div x-cloak x-show="editChipModal" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
            @click.away="editChipModal = false">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">
                {{ __('msg.edit') }}{{ __('msg.chip') }}{{ __('msg.machine') }}</h2>
            <div class="w-full mb-3 flex items-center">
                <label class="block text-sm font-medium text-gray-700">金鑰</label>
                <div class="mt-1 p-2 bg-gray-100 rounded-md text-gray-500" x-text="selectedChip.token"></div>
                <label for="machine_id" class="ps-5 inline-block text-sm font-medium text-gray-700">機器編號</label>
                <div class="mt-1 p-2 bg-gray-100 rounded-md text-gray-500" x-text="selectedChip.machine_id"></div>

                <form action="{{ route('admin.avtiveMachine') }}" method="POST" class="ps-2 inline-block items-center"
                    x-on:submit="if(!confirm(`Are you sure you want to ${selectedChip.is_active ? 'deactivate' : 'activate'} this user?`)) return false;">
                    @csrf
                    <input type="hidden" name="is_active" x-bind:value="selectedChip.is_active ? 0 : 1">
                    <input type="hidden" name="token" x-bind:value="selectedChip.token">
                    <button type="submit" class="inline-block">
                        <template x-if="selectedChip.is_active">
                            <x-svg-icons name="statusT" classes="h-6 w-6" />
                        </template>
                        <template x-if="!selectedChip.is_active">
                            <x-svg-icons name="statusF" classes="h-6 w-6" />
                        </template>
                    </button>
                </form>
            </div>

            <form x-bind:action="`{{ route('admin.connectMachineUpdate') }}`" method="POST">
                @csrf
                @method('POST')<!-- 隱藏欄位傳遞 chip id -->
                <input type="hidden" name="id" x-model="selectedChip.id">
                <!-- 修改name -->
                <div class="w-full mb-3">
                    <label for="name" class="block text-sm font-medium text-gray-700">修改機台名稱</label>
                    <input type="text" name="name" id="mname"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        x-model="selectedChip.name">
                </div>
                <!-- 修改商鋪 -->
                <div class="w-full mb-3">
                    @if (Auth::user()->hasRole('store-owner'))
                        @if (Auth::user()->storeable_type === 'App\Models\Store')
                            <input type="hidden" name="arcade_id"
                                value="{{ \App\Models\Store::find(Auth::user()->storeable_id)->id ?? '' }}">
                        @elseif(Auth::user()->storeable_type === 'App\Models\VsStore')
                            <input type="hidden" name="arcade_id"
                                value="{{ \App\Models\VsStore::find(Auth::user()->storeable_id)->id ?? '' }}">
                        @endif
                    @elseif(Auth::user()->hasRole('admin'))
                        <label for="storeable_id" class="block text-sm font-medium text-gray-700">
                            修改機器所在店鋪
                        </label>
                        <select name="storeable_id" id="storeable_id" x-model="selectedChip.storeable_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            onchange="updateStoreType()" required>
                            <optgroup label="店鋪 (Store)">
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}" data-type="App\Models\Store">{{ $store->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="虛擬店鋪 (Virtual Store)">
                                @foreach ($vsStores as $vsStore)
                                    <option value="{{ $vsStore->id }}" data-type="App\Models\VsStore">
                                        自-{{ $vsStore->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    @else
                        <input type="hidden" name="arcade_id" value="{{ Auth::user()->parent->store->id ?? '' }}">
                    @endif
                </div>
                <!-- 修改revenue_split -->
                <div class="w-full mb-3">
                    <label for="revenue_split" class="block text-sm font-medium text-gray-700">修改分成比例</label>
                    <input type="number" name="revenue_split" id="revenue_split" step="5"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        x-model="selectedChip.revenue_split">
                </div>
                <!-- 修改type -->
                <div class="w-full mb-3">
                    <label for="machine_type" class="block text-sm font-medium text-gray-700">修改機台類型</label>
                    <input type="text" name="machine_type" id="machine_type"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        x-model="selectedChip.type">
                </div>

                <!-- 其他需要修改的欄位依需求加入 -->
                <!-- 提交按鈕 -->
                <div class="flex justify-end">
                    <button type="submit" class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        儲存變更
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
