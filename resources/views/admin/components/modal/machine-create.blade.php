@props(['title', 'formAction', 'machine' => null, 'arcades', 'users', 'machineAuthKeys'])

<div x-data="{ showModal: false }" @open-create-machine-modal.window="showModal = true" @close-modal.window="showModal = false">
    <!-- Modal backdrop -->
    <div x-show="showModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" x-cloak>
    </div>

    <!-- Modal panel -->
    <div x-show="showModal" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg"
                role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-headline">
                        {{ $title }} 
                    </h3>
                    <button @click="showModal = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ $formAction }}" method="POST" class="mt-4">
                    @csrf
                    @if ($machine)
                        @method('PATCH') {{-- 或者 'PUT' --}}
                    @endif

                    <div class="mb-4">
                        <label for="create_chipKey"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.chip_token') }}</label>
                        <select id="create_chipKey" name="chipKey"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="">{{ __('msg.select') }} {{ __('msg.chip_token') }}</option>
                            @foreach ($machineAuthKeys as $key)
                                <option value="{{ $key->auth_key }}" @if (old('chipKey', $machine?->machineAuthKey?->auth_key) == $key->auth_key) selected @endif>
                                    {{ $key->auth_key }} ({{ $key->status }})
                                </option>
                            @endforeach
                        </select>
                        @error('chipKey')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="create_chip_hardware_id"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.chip_hardware_id') }}</label>
                        <input type="text" name="chip_hardware_id" id="create_chip_hardware_id"
                            value="{{ old('chip_hardware_id', $machine?->machineAuthKey?->chip_hardware_id) }}"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                        @error('chip_hardware_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="create_machine_name"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.machine_name') }}</label>
                        <input type="text" name="name" id="create_machine_name"
                            value="{{ old('name', $machine?->name) }}"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                    </div>
                    <!-- Machine Type and conditional fields -->
                    <div class="mb-4" x-data="{ currentMachineType: '{{ old('machine_type', $machine?->machine_type ?? 'pinball') }}' }">
                        <label for="create_machine_type"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.machine_type') }}</label>
                        <select id="create_machine_type" name="machine_type" x-model="currentMachineType"
                            class="w-full mt-1 p-2 border rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="pinball" @if (old('machine_type', $machine?->machine_type ?? 'pinball') == 'pinball') selected @endif>
                                {{ __('msg.pachinko') }} (彈珠台)</option>
                            <option value="claw" @if (old('machine_type', $machine?->machine_type) == 'claw') selected @endif>
                                {{ __('msg.claw_machine') }} (娃娃機)</option>
                            <option value="points_redemption" @if (old('machine_type', $machine?->machine_type) == 'points_redemption') selected @endif>積分兌換型
                            </option>
                            <option value="ticket_redemption" @if (old('machine_type', $machine?->machine_type) == 'ticket_redemption') selected @endif>彩票兌換型
                            </option>
                            <option value="normally" @if (old('machine_type', $machine?->machine_type) == 'normally') selected @endif>
                                {{ __('msg.machine_type_general') }} (通用型)</option>
                        </select>

                        <div class="mt-4">
                            <label for="create_credit_value_override"
                                class="block text-sm font-medium text-gray-700">覆寫每 Credit 價值
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="credit_value_override" id="create_credit_value_override"
                                step="0.01" min="0"
                                value="{{ old('credit_value_override', $machine?->credit_value_override) }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="{{ \App\Models\SystemSetting::getValue(\App\Http\Controllers\Admin\SystemSettingController::KEY_DEFAULT_CREDIT_VALUE, 10) }}">
                            <p class="mt-1 text-sm text-gray-500">若留空，將使用系統預設值。</p>
                        </div>

                        <div class="mt-4" x-show="currentMachineType === 'pinball'">
                            <label for="create_balls_per_credit_override"
                                class="block text-sm font-medium text-gray-700">覆寫每 Credit 出珠數 (顆)
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="balls_per_credit_override" id="create_balls_per_credit_override"
                                step="1" min="1"
                                value="{{ old('balls_per_credit_override', $machine?->balls_per_credit_override) }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="{{ \App\Models\SystemSetting::getValue(\App\Http\Controllers\Admin\SystemSettingController::KEY_DEFAULT_BALLS_PER_CREDIT, 10) }}">
                            <p class="mt-1 text-sm text-gray-500">僅適用於彈珠台類型。若留空，將使用系統預設值。</p>
                        </div>

                        <div class="mt-4"
                            x-show="currentMachineType === 'points_redemption' || currentMachineType === 'ticket_redemption'">
                            <label for="create_points_per_credit_action_override"
                                class="block text-sm font-medium text-gray-700">覆寫每次 Credit 操作獲得點數
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="points_per_credit_action_override"
                                id="create_points_per_credit_action_override" step="1" min="1"
                                value="{{ old('points_per_credit_action_override', $machine?->points_per_credit_action_override) }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="例如：開分一次得100點">
                            <p class="mt-1 text-sm text-gray-500">適用於積分或彩票兌換型機器。</p>
                        </div>

                        <div class="mt-4"
                            x-show="currentMachineType === 'points_redemption' || currentMachineType === 'ticket_redemption' || currentMachineType === 'claw'">
                            <label for="create_payout_type_override"
                                class="block text-sm font-medium text-gray-700">覆寫獎品兌換類型
                                ({{ __('msg.optional') }})</label>
                            <select name="payout_type_override" id="create_payout_type_override"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('msg.select') }}</option>
                                <option value="points" @if (old('payout_type_override', $machine?->payout_type_override) == 'points') selected @endif>積分</option>
                                <option value="tickets" @if (old('payout_type_override', $machine?->payout_type_override) == 'tickets') selected @endif>彩票</option>
                                <option value="tokens" @if (old('payout_type_override', $machine?->payout_type_override) == 'tokens') selected @endif>代幣</option>
                                <option value="prize" @if (old('payout_type_override', $machine?->payout_type_override) == 'prize') selected @endif>獎品 (娃娃機適用)
                                </option>
                                <option value="none" @if (old('payout_type_override', $machine?->payout_type_override) == 'none') selected @endif>無兌換</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">定義機器吐出的獎品是什麼類型。</p>
                        </div>

                        <div class="mt-4"
                            x-show="currentMachineType === 'points_redemption' || currentMachineType === 'ticket_redemption' || currentMachineType === 'claw'">
                            <label for="create_payout_value_per_unit_override"
                                class="block text-sm font-medium text-gray-700">覆寫每單位獎品價值
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="payout_value_per_unit_override"
                                id="create_payout_value_per_unit_override" step="0.01" min="0"
                                value="{{ old('payout_value_per_unit_override', $machine?->payout_value_per_unit_override) }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="例如：每張彩票價值0.5元">
                            <p class="mt-1 text-sm text-gray-500">定義每單位獎品 (如彩票、積分、獎品成本) 的貨幣價值。</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="create_arcade_id"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.select_arcade') }}:</label>
                        <select id="create_arcade_id" name="arcade_id"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="">{{ __('msg.select') }} {{ __('msg.arcade') }}</option>
                            @foreach ($arcades as $arcade)
                                <option value="{{ $arcade->id }}"
                                    @if (old('arcade_id', $machine?->arcade_id) == $arcade->id) selected @endif>{{ $arcade->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="create_owner_id"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.select_machine_owner') }}:</label>
                        <select id="create_owner_id" name="owner_id"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="">{{ __('msg.select') }} {{ __('msg.owner') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    @if (old('owner_id', $machine?->owner_id) == $user->id) selected @endif>{{ $user->name }}
                                    ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $machine ? __('msg.update') : __('msg.create') }}
                        </button>
                        <button type="button" @click="showModal = false"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            {{ __('msg.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
