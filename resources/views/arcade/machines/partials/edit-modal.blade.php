<!-- Edit Machine Modal -->
<div x-cloak x-show="showEditMachineModal" class="fixed inset-0 z-50 overflow-y-auto pb-5"
    @keydown.escape.window="closeEditModal()" role="dialog" aria-modal="true" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div @click="closeEditModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

            <form :action="'{{ route('arcade.machines.update', '__ID__') }}'.replace('__ID__', editForm.id)"
                method="POST" id="editMachineForm">
                @csrf
                @method('PATCH')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ __('msg.edit') }} {{ __('msg.machine') }}:
                        <span x-text="editForm.name"></span>
                    </h3>
                    <div class="mt-4 space-y-4">

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name_edit_arcade"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                            <input type="text" name="name" id="name_edit_arcade"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                x-model="editForm.name" required>
                            @error('name')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Arcade -->
                        <div class="mb-4">
                            <label for="arcade_id_edit_arcade"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.select_arcade') }}</label>
                            <select id="arcade_id_edit_arcade" name="arcade_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                x-model="editForm.arcade_id" required>
                                <option value="">{{ __('msg.select_arcade') }}</option>
                                @foreach ($arcades ?? [] as $arcade)
                                    <option value="{{ $arcade->id }}">{{ $arcade->name }}</option>
                                @endforeach
                            </select>
                            @error('arcade_id')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Chip Hardware ID and Auth Key (like create-modal) -->
                        <div class="flex flex-row sm:flex-row gap-4">
                            <div class="w-[40%]">
                                <label for="edit_chip_hardware_id_modal_arcade"
                                    class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.chip_hardware_id') }}
                                </label>
                                <input type="text" id="edit_chip_hardware_id_modal_arcade"
                                    x-model="editForm.chip_hardware_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    readonly>
                                @error('chip_hardware_id')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="w-[60%]">
                                <label for="auth_key_edit_display_arcade"
                                    class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.machine_auth_key_display_label') }}
                                </label>
                                <input type="text" id="auth_key_edit_display_arcade"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 cursor-not-allowed focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    x-bind:value="editForm.auth_key || 'N/A'" readonly>
                            </div>
                        </div>

                        <!--***** 遊戲機設定 (Wizard-based) ******-->
                        <div class="border border-gray-200 p-4 rounded-md space-y-4">
                            <!-- Step 1: Machine Category -->
                            <div>
                                <label for="edit_machine_category" class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.main_op_mode') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="machine_category" id="edit_machine_category"
                                    x-model="editForm.machine_category" @change="updateEditFormBasedOnCategory()"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">{{ __('msg.please_select') }}</option>
                                    @foreach (config('machines.templates', []) as $key => $template)
                                        <option value="{{ $key }}">{{ __($template['display']) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Step 2: Follow-up questions -->
                            <div x-show="editForm.machine_category">
                                <!-- Payout Type Selection -->
                                <div x-show="editForm.payout_type_selection.length > 0">
                                    <label for="edit_payout_type" class="block text-sm font-medium text-gray-700">
                                        {{ __('msg.payout_type') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="payout_type" id="edit_payout_type" x-model="editForm.payout_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">{{ __('msg.please_select') }}</option>
                                        <template x-for="payout in editForm.payout_type_selection"
                                            :key="payout.value">
                                            <option :value="payout.value" x-text="payout.text"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Optional Modules -->
                                <div x-show="editForm.optional_modules.length > 0" class="mt-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.optional_modules') }}</label>
                                    <div class="mt-2 space-y-2">
                                        <template x-for="module in editForm.optional_modules" :key="module.value">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" :name="'modules[' + module.value + ']'"
                                                    :value="module.value" x-model="editForm.selected_modules"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600" x-text="module.text"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Machine Type (Appearance) -->
                            <div class="mt-4">
                                <label for="edit_machine_type" class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.machine_appearance_type') }}
                                </label>
                                <input type="text" name="machine_type" id="edit_machine_type"
                                    x-model="editForm.machine_type" placeholder="{{ __('msg.optional_input') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <div x-show="editForm.machine_category && editForm.machine_category !== 'management'">
                                <!-- Credit and Balls Configuration -->
                                <div class="CBC flex flex-col sm:flex-row gap-4 mb-4">
                                    <div
                                        class="cbckid flex items-center space-x-2 text-sm text-gray-700 order-2 sm:order-1">
                                        {{-- 一枚代幣價值 --}}
                                        {{-- <input type="checkbox" x-model="editForm.credit_value_enabled"
                                            :disabled="editForm.payout_type === 'none'"
                                            :class="{ 'opacity-50': editForm.payout_type === 'none' }"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> --}}
                                        <span class="text-gray-700">{{ __('msg.one_token_equals') }}</span>
                                        <select name="coin_input_value" id="edit_coin_input_value_arcade"
                                            x-model="editForm.coin_input_value"
                                            class="w-20 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                            @foreach ([0, 10, 20, 50, 100, 200, 500, 1000, 5000] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-gray-700"
                                            x-text="createForm.arcade_currency || '{{ __('msg.currency_unit') }}'">
                                        </span>
                                        {{-- <p class="mt-1 text-xs text-gray-500">
                                            ({{ __('msg.empty_uses_default') }})</p> --}}
                                    </div>
                                    <!-- 隨選擇變化 -->
                                    <div x-show="editForm.payout_type && editForm.payout_type !== 'none'"
                                        class="cssbvpar flex items-center justify-end ml-auto order-1 sm:order-2 -mt-3">
                                        <template x-if="editForm.payout_type === 'ball'">
                                            <div class="cssbv flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('一顆小鋼珠價值') }}</span>
                                                <select name="payout_unit_value" id="edit_ball_input_value_arcade"
                                                    x-model="editForm.payout_unit_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1, 1.5, 2, 5, 10] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>
                                        <template x-if="editForm.payout_type === 'tickets'">
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('每張彩票價值') }}</span>
                                                <select name="payout_unit_value" id="edit_ticket_value_arcade"
                                                    x-model="editForm.payout_unit_value"
                                                    class="w-24 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1, 1.5, 2, 5, 10] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>
                                        <template x-if="editForm.payout_type === 'points'">
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('每一點數價值') }}</span>
                                                <select name="payout_unit_value" id="edit_point_value_arcade"
                                                    x-model="editForm.payout_unit_value"
                                                    class="w-24 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1, 1.5, 2, 5, 10, 20, 30, 50, 100, 500, 1000] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>
                                        <template x-if="editForm.payout_type === 'prize'">
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('每ㄧ獎品均價') }}</span>
                                                <select name="payout_unit_value" id="edit_prize_value_arcade"
                                                    x-model="editForm.payout_unit_value"
                                                    class="w-24 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1.5, 2, 5, 10, 20, 30, 50, 100] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- 開洗分 Configuration -->
                                <div class="flex flex-col sm:flex-row gap-4 order-3">
                                    <div class="flex items-center space-x-2 text-sm text-gray-700">
                                        {{-- <input type="checkbox" x-model="editForm.points_tickets_has_credit_action_config"
                                            :disabled="editForm.payout_type === 'none'"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> --}}
                                        <span>{{ __('msg.per_credit_action') }}</span>
                                        <select name="credit_button_value" id="edit_credit_button_value_arcade"
                                            x-model="editForm.credit_button_value"
                                            class="w-24 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                            @foreach ([0, 50, 100, 200, 500, 1000, 5000] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span>{{ __('msg.currency_unit') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-700">
                                        {{-- <input type="checkbox" x-model="editForm.create_credit_out_enable"
                                            :disabled="editForm.payout_type === 'none'"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> --}}
                                        <span>{{ __('msg.payout_value') }}</span>
                                        <select name="payout_button_value" id="edit_payout_button_value_arcade"
                                            x-model="editForm.payout_button_value"
                                            class="w-24 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                            @foreach ([0, 50, 100, 200, 500, 1000, 5000] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span>{{ __('msg.currency_unit') }}</span>
                                    </div>
                                </div>

                                <!-- balls_per_credit 相關的 HTML 已移除 -->
                            </div>

                            <!-- 在 money_slot 特定設定之前添加 -->
                            <input type="hidden" name="accepted_denominations" value=""
                                x-show="editForm.machine_type !== 'money_slot'">

                            <div x-show="editForm.machine_type === 'money_slot'"
                                class="border border-blue-300 p-4 rounded-md space-y-4 mt-4">
                                <!-- 現有 money_slot 設定 -->
                                <h4 class="text-md font-semibold text-blue-700">
                                    {{ __('msg.money_slot') }} {{ __('msg.specific_settings') }}
                                </h4>
                                <div>
                                    <label for="edit_bill_currency_money_slot"
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.bill_currency') }}</label>
                                    <select name="bill_currency" id="edit_bill_currency_money_slot"
                                        x-model="editForm.bill_currency"
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @foreach (config('bill_mappings', []) as $code => $displayNameKey)
                                            <option value="{{ $code }}">
                                                {{ __('msg.' . $code) }}
                                                ({{ __($code) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bill_currency')
                                        {{-- Assuming you might add validation --}}
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div x-show="editForm.bill_currency && available_denominations_for_selected_currency.length > 0"
                                    class="mt-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.accepted_denominations') }}</label>

                                    <div class="px-3 mt-2 flex flex-wrap gap-x-4 gap-y-2 justify-start">
                                        <label class="inline-flex items-center w-full mb-2 sm:w-auto">
                                            <input type="checkbox" x-model="editForm.all_denominations_selected"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span
                                                class="ml-2 text-sm font-medium text-gray-700">{{ __('msg.select_all') }}
                                            </span>
                                        </label>
                                        <template x-for="denomination in available_denominations_for_selected_currency"
                                            :key="denomination">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" :value="denomination"
                                                    x-model="editForm.accepted_denominations"
                                                    name="accepted_denominations[]"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600"
                                                    x-text="formatNumberWithCommas(denomination)"></span>
                                            </label>
                                        </template>
                                    </div>
                                    @error('accepted_denominations')
                                        {{-- Assuming you might add validation --}}
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class=" py-5 flex flex-row space-x-2">
                            <!-- Revenue Split -->
                            <div class="mb-4 w-[50%]">
                                <label for="edit_revenue_split_arcade"
                                    class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.revenue_split') }}% <span class="text-red-500">*</span>
                                </label>
                                <select name="revenue_split" id="edit_revenue_split_arcade"
                                    x-model="editForm.revenue_split" required
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach (range(5, 50, 5) as $value)
                                        {{-- Assuming max 50% for owner split, adjust if needed --}}
                                        <option value="{{ $value }}">{{ $value }}%</option>
                                    @endforeach
                                </select>
                                @error('revenue_split')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Platform Share Percentage for Machine -->
                            <div class="mb-4 min-w-[40%]">
                                <label for="edit_share_pct" class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.platform_machine_share_pct') }}%
                                </label>
                                <div class="mt-1 flex items-center">
                                    <select name="share_pct" id="edit_share_pct" x-model="editForm.share_pct"
                                        class="block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">{{ __('msg.default') }} / {{ __('msg.not_set') }}
                                        </option>

                                        @foreach (range(0.0, 10.0, 0.5) as $value)
                                            <option value="{{ number_format($value, 1) }}">
                                                {{ number_format($value, 1) }}%</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('share_pct')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Owner (Display Only for Arcade context) -->
                    <div class="mb-4 pt-2">
                        @if (Auth::user()->hasRole('admin'))
                            @php
                                $allOwners = collect($admins ?? [])
                                    ->merge($arcadeOwners ?? [])
                                    ->merge($machineOwners ?? [])
                                    ->unique('id')
                                    ->sortBy('name');
                            @endphp
                            <label for="owner_id"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                            <select name="owner_id" id="owner_id"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                x-model="editForm.owner_id" required>
                                <option value="" class="text-gray-500 text-sm font-thin">
                                    {{ __('msg.select') }} {{ __('msg.owner') }}
                                </option>

                                @foreach ($allOwners as $user)
                                    <option value="{{ $user->id }}"
                                        :selected="editForm.owner_id === '{{ $user->id }}'">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <label for="owner_id_edit_display_arcade"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                            <span
                                class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed p-2"
                                x-text="editForm.owner?.name || 'N/A'"></span>
                            <input type="hidden" name="owner_id" x-model="editForm.owner_id">
                        @endif
                    </div>
                </div>
                <!-- Buttons -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('msg.save') }}
                    </button>
                    <button type="button" @click="closeEditModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        {{ __('msg.cancel') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
