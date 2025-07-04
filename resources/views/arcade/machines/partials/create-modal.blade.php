<!-- Create Machine Modal -->
<style>
    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        padding: 0.5rem 0.75rem;
        min-width: 70px;
    }
</style>
<div x-cloak x-show="showCreateMachineModal" @keydown.escape.window="closeCreateModal()" wire:ignore.self
    class="fixed inset-0 z-50 overflow-y-auto pb-5" role="dialog" aria-modal="true"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div @click="closeCreateModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form method="POST" action="{{ route('arcade.machines.store') }}" id="createMachineForm"
                @submit.prevent="submitCreate()">
                @csrf
                {{-- 如果是員工新增機器,owner也會是老闆的ID --}}
                <input type="hidden" name="owner_id"
                    value="{{ auth()->user()->isArcadeStaff() && auth()->user()->parent ? auth()->user()->parent->id : auth()->id() ?? 'null' }}">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('msg.add') }} {{ __('msg.machine') }}
                    </h3>
                    <div class="mt-4 space-y-4">
                        <!-- 1Name -->
                        <div>
                            <label for="create_name" class="block text-sm font-medium text-gray-700">
                                {{ __('msg.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="create_name" x-model="createForm.name" required
                                class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('name')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 2Arcade -->
                        <div>
                            <label for="create_arcade_id"
                                class="block text-sm font-medium text-gray-700">
                                {{ __('msg.arcade') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="arcade_id" id="create_arcade_id" x-model="createForm.arcade_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">{{ __('msg.select_arcade') }}</option>
                                @foreach ($arcades as $arcade)
                                    <option value="{{ $arcade->id }}">{{ $arcade->name }}</option>
                                @endforeach
                            </select>
                            @error('arcade_id')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 3Chip Hardware ID and Auth Key -->
                        <div class="flex gap-4">
                            <div class="w-[40%]">
                                <x-machine-attr name="chip_hardware_id" label="msg.chip_hardware_id" required="true"
                                    placeholder="msg.chip_hardware_id_placeholder"
                                    model="createForm.chip_hardware_id" />
                            </div>
                            <div class="w-[60%]">
                                <x-machine-attr name="auth_key" label="msg.chip_token" required="true"
                                    placeholder="msg.paste_chip_url_or_id" model="createForm.auth_key" width="60%"
                                    hasButton="true" buttonAction="generateCreateKey()"
                                    isLoading="isLoadingCreateKey" />
                            </div>
                        </div>

                        <!--*****m01 遊戲機-******-->
                        {{-- <div x-show="createForm.machine_type !== 'money_slot'"
                            class="border border-gray-200 p-4 rounded-md space-y-4 "> --}}

                        <div class="border border-gray-200 p-4 rounded-md space-y-4 ">
                            <!-- Machine Type and Payout Type -->
                            <div class="MTP flex flex-col sm:flex-row gap-4">
                                <div class="flex-1 機器類型">
                                    <x-machine-attr name="machine_type" label="msg.machine_type" type="select"
                                        required="true" model="createForm.machine_type" :options="config('machines.types', [])" />
                                </div>
                                <div x-show="!createForm.machine_type || createForm.machine_type !== 'money_slot'"
                                    class="獎品類型 flex-1">
                                    <x-machine-attr name="payout_type" label="msg.payout_type" type="select"
                                        required="true" model="createForm.payout_type" :options="[
                                            'none' => 'msg.select',
                                            'ball' => 'msg.payout_type_pachinko',
                                            'points' => 'msg.payout_type_points',
                                            'tickets' => 'msg.payout_type_tickets',
                                            'coins' => 'msg.payout_type_coins',
                                            'prize' => 'msg.payout_type_prize_claw',
                                        ]" />
                                </div>
                            </div>

                            <div x-show="!createForm.machine_type || createForm.machine_type !== 'money_slot'">
                                <!-- Credit and Balls Configuration -->
                                <div class="CBC flex flex-col flex-row  justify-between sm:flex-row gap-4 mb-4">
                                    <div
                                        class="cbckid flex items-center space-x-2 text-sm text-gray-700 order-2 sm:order-1">
                                        <input type="checkbox" x-model="createForm.coin_input_value_enabled"
                                            :disabled="createForm.payout_type === 'none'" id="cb_coin"
                                            :class="{ 'opacity-50': createForm.payout_type === 'none' }"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span
                                            :class="{
                                                'text-gray-700': createForm
                                                    .coin_input_value_enabled,
                                                'text-gray-400': !createForm
                                                    .coin_input_value_enabled
                                            }">{{ __('msg.one_token_equals') }}</span>
                                        <select name="coin_input_value" id="create_coin_value"
                                            x-model="createForm.coin_input_value"
                                            :disabled="!createForm.coin_input_value_enabled"
                                            class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1"
                                            :class="{ 'opacity-50': !createForm.coin_input_value_enabled }">
                                            @foreach ([0, 10, 20, 50, 100, 200, 500, 1000, 5000] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span
                                            :class="{
                                                'text-gray-700': createForm
                                                    .coin_input_value_enabled,
                                                'text-gray-400': !createForm
                                                    .coin_input_value_enabled
                                            }"
                                            x-text="createForm.arcade_currency || '{{ __('msg.currency_unit') }}'">
                                            {{ __('msg.currency_unit') }}
                                        </span>
                                    </div>
                                    <!-- 隨選擇變化 -->
                                    <div x-show="createForm.payout_type && createForm.payout_type !== 'none'"
                                        class="cssbvpar flex items-center justify-end ml-auto order-1 sm:order-2 -mt-3">
                                        <template x-if="createForm.payout_type === 'ball'">
                                            <div
                                                class="cssbv flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('msg.per_ball_value') }}</span>
                                                <select name="payout_unit_value" id="create_ball_input_value"
                                                    x-model="createForm.ball_input_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1, 1.5, 2, 5, 10] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>

                                        <template x-if="createForm.payout_type === 'tickets'">
                                            <div
                                                class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('msg.per_ticket_value') }}</span>
                                                <select name="payout_unit_value" id="create_ticket_value"
                                                    x-model="createForm.ticket_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1, 1.5, 2, 5, 10] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>

                                        <template x-if="createForm.payout_type === 'points'">
                                            <div
                                                class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('msg.per_point_value') }}</span>
                                                <select name="payout_unit_value" id="create_point_value"
                                                    x-model="createForm.point_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.1, 0.5, 1, 1.5, 2, 5, 10, 20, 50, 100, 500, 1000] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>

                                        <template x-if="createForm.payout_type === 'prize'">
                                            <div
                                                class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('msg.per_prize_value') }}</span>
                                                <select name="payout_unit_value" id="create_prize_value"
                                                    x-model="createForm.prize_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
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
                                <div class="flex flex-col flex-row sm:justify-between sm:flex-row gap-4 order-3">
                                    <!-- 開分-->
                                    <div
                                        class="css_credit flex items-center space-x-2 text-sm text-gray-700">
                                        <input type="checkbox" x-model="createForm.credit_in_enable"
                                            :disabled="createForm.payout_type === 'none'"
                                            id="create_credit_action_enable"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span
                                            :class="{
                                                'text-gray-700': createForm.credit_in_enable,
                                                'text-gray-400': !createForm
                                                    .credit_in_enable // 嘗試不同的灰色
                                            }">{{ __('msg.per_credit_action') }}</span>
                                        <select name="credit_button_value" id="create_credit_button_value_pinball"
                                            x-model="createForm.credit_button_value"
                                            :disabled="!createForm.credit_in_enable"
                                            class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1"
                                            :disabled="createForm.payout_type === 'none'"
                                            :class="{ 'opacity-50': createForm.payout_type === 'none' }"
                                            :class="{
                                                'opacity-50': !createForm.credit_in_enable,
                                                'text-gray-700': createForm.credit_in_enable,
                                                'text-gray-400': !createForm
                                                    .credit_in_enable // 嘗試不同的灰色
                                            }">
                                            @foreach ([0, 50, 100, 200, 500, 1000, 5000] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span
                                            :class="{
                                                'text-gray-700': createForm
                                                    .credit_in_enable,
                                                {{-- <--- 修改點 --}} 'text-gray-300': !createForm
                                                    .credit_in_enable {{-- <--- 修改點 --}}
                                            }">{{ __('msg.currency_unit') }}</span>
                                    </div>
                                    <!-- 洗分-->
                                    <div class="flex items-center space-x-2 text-sm text-gray-700">
                                        <input type="checkbox" x-model="createForm.create_credit_out_enable"
                                            :disabled="createForm.payout_type === 'none'"
                                            id="create_payout_action_enable" {{-- 修改點：給洗分 checkbox 一個唯一的 ID --}}
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span
                                            :class="{
                                                'text-gray-700': createForm
                                                    .create_credit_out_enable,
                                                'text-gray-400': !createForm
                                                    .create_credit_out_enable
                                            }">{{ __('msg.payout_value') }}</span>
                                        <select name="payout_button_value"
                                            id="create_points_tickets_payout_button_value_pinball"
                                            x-model="createForm.payout_button_value"
                                            :disabled="!createForm.create_credit_out_enable"
                                            class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1"
                                            :disabled="createForm.payout_type === 'none'"
                                            :class="{ 'opacity-50': createForm.payout_type === 'none' }"
                                            :class="{
                                                'opacity-50': !createForm.create_credit_out_enable,
                                                'text-gray-700': createForm
                                                    .create_credit_out_enable,
                                                'text-gray-400': !createForm
                                                    .create_credit_out_enable
                                            }">
                                            {{--  :class="{ 'opacity-50': !createForm.create_credit_out_enable }"> --}}
                                            @foreach ([0, 50, 100, 200, 500, 1000, 5000] as $value)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        <span
                                            :class="{
                                                'text-gray-700': createForm
                                                    .create_credit_out_enable,
                                                'text-gray-400': !createForm
                                                    .create_credit_out_enable
                                            }">{{ __('msg.currency_unit') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Revenue Split -->
                                <div class="pt-3">
                                    <label for="edit_revenue_split_arcade"
                                        class="block text-sm font-medium text-gray-700">
                                        {{ __('msg.revenue_split') }}% <span class="text-red-500">*</span>
                                    </label>
                                    <select name="revenue_split" id="edit_revenue_split_arcade"
                                        x-model="editForm.revenue_split" required
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @foreach (range(5, 100, 5) as $value)
                                            <option value="{{ $value }}">{{ $value }}%</option>
                                        @endforeach
                                    </select>
                                    @error('revenue_split')
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <!-- m02紙鈔轉 -->
                        <!--***** 儲值收銀機 (money_slot) 特定設定 ******-->
                        <div x-show="createForm.machine_type === 'money_slot'"
                            class="border border-blue-300 p-4 rounded-md space-y-4 mt-4">
                            <h4 class="text-md font-semibold text-blue-700">
                                {{ __('msg.money_slot') }} {{ __('msg.specific_settings') }}
                            </h4>
                            <div>
                                <label for="create_bill_currency_money_slot"
                                    class="block text-sm font-medium text-gray-700">{{ __('msg.bill_currency') }}</label>
                                <select name="bill_currency" id="create_bill_currency_money_slot"
                                    x-model="createForm.bill_currency"
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach (config('bill_mappings', []) as $code => $displayNameKey)
                                        <option value="{{ $code }}">
                                            {{ __('msg.' . $code) }}
                                            ({{ __($code) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- 紙鈔單位價值 (bill_unit_value) - 用戶手動輸入 --}}
                            <div x-show="createForm.bill_currency && available_denominations_for_selected_currency.length > 0"
                                class="mt-4">
                                <label
                                    class="block text-sm font-medium text-gray-700">{{ __('msg.accepted_denominations') }}</label>

                                <div class="px-3 mt-2 flex flex-wrap gap-x-4 gap-y-2 justify-between">
                                    <label class="inline-flex items-center"><input type="checkbox"
                                            x-model="createForm.all_denominations_selected"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span
                                            class="ml-2 text-sm font-medium text-gray-700">{{ __('msg.select_all') }}
                                        </span></label>
                                    {{-- 使用 flexbox 實現水平排列和換行 --}}
                                    <template x-for="denomination in available_denominations_for_selected_currency"
                                        :key="denomination">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" :value="denomination"
                                                x-model="createForm.accepted_denominations"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="m1-2 text-sm text-gray-600"
                                                x-text="formatNumberWithCommas(denomination)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('msg.save') }}
                        </button>
                        <button type="button" @click="closeCreateModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            {{ __('msg.cancel') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
