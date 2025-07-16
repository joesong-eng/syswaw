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
            <form action="{{ route('admin.machines.store') }}" method="POST" id="editMachineForm"
                enctype="multipart/form-data">
                @csrf
                {{-- 如果是員工新增機器,owner也會是老闆的ID --}}
                <input type="hidden" name="owner_id"
                    value="{{ auth()->user()->isArcadeStaff() && auth()->user()->parent ? auth()->user()->parent->id : auth()->id() ?? 'null' }}">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('msg.add') }} {{ __('msg.machine') }}
                    </h3>
                    <div class="mt-4 space-y-4">
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

                        <div>
                            <label for="create_arcade_id" class="block text-sm font-medium text-gray-700">
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

                        <div class="flex gap-4">
                            <div class="w-[40%]">
                                <x-machine-attr name="chip_hardware_id" label="msg.chip_hardware_id" required="true"
                                    placeholder="例如：abc123_-@." model="createForm.chip_hardware_id"
                                    pattern="[a-zA-Z0-9_\-\.@]{1,10}" title="請輸入 1-10 位的英文、數字、下滑線、連字符、點或 @ 符號"
                                    x-on:input="createForm.chip_hardware_id = $event.target.value; if (!/^[a-zA-Z0-9_\-\.@]{1,10}$/.test($event.target.value)) { $el.setCustomValidity('通訊卡 ID 格式無效，請使用 1-10 位的英文、數字、下滑線、連字符、點或 @ 符號'); } else { $el.setCustomValidity(''); }" />
                            </div>
                            <div class="w-[60%]">
                                <x-machine-attr name="auth_key" label="msg.chip_token" required="true"
                                    placeholder="msg.paste_chip_url_or_id" model="createForm.auth_key" hasButton="true"
                                    buttonAction="generateCreateKey()" isLoading="isLoadingCreateKey" />
                            </div>
                        </div>

                        {{-- <div x-show="createForm.machine_type !== 'money_slot'"
                            class="border border-gray-200 p-4 rounded-md space-y-4 "> --}}

                        <div x-show="createForm.machine_category !== 'utility'"
                            class="border border-gray-200 p-4 rounded-md space-y-4">
                            <div>
                                <label for="create_machine_category" class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.main_op_mode') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="machine_category" id="create_machine_category"
                                    x-model="createForm.machine_category" @change="updateCreateFormBasedOnCategory()"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">{{ __('msg.please_select') }}</option>
                                    @foreach (config('machines.templates', []) as $key => $template)
                                        <option value="{{ $key }}">{{ __($template['display']) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="createForm.machine_category">
                                <div x-show="createForm.payout_type_selection.length > 0">
                                    <label for="create_payout_type" class="block text-sm font-medium text-gray-700">
                                        {{ __('msg.payout_type') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="payout_type" id="create_payout_type" x-model="createForm.payout_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">{{ __('msg.please_select') }}</option>
                                        <template x-for="payout in createForm.payout_type_selection"
                                            :key="payout.value">
                                            <option :value="payout.value" x-text="payout.text"></option>
                                        </template>
                                    </select>
                                </div>

                                <div x-show="createForm.optional_modules.length > 0" class="mt-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.optional_modules') }}</label>
                                    <div class="mt-2 space-y-2">
                                        <template x-for="module in createForm.optional_modules" :key="module.value">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" :name="'modules[' + module.value + ']'"
                                                    :value="module.value" x-model="createForm.selected_modules"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600" x-text="module.text"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div x-show="createForm.machine_category !== 'utility'" class="mt-4">
                                <label for="create_machine_type" class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.machine_appearance_type') }}
                                </label>
                                <input type="text" name="machine_type" id="create_machine_type"
                                    x-model="createForm.machine_type" placeholder="{{ __('msg.optional_input') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <div x-show="createForm.machine_category !== 'utility'">
                                <div class="CBC flex flex-col justify-between sm:flex-row gap-4 mb-4">
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
                                    <div x-show="createForm.payout_type && createForm.payout_type !== 'none'"
                                        class="cssbvpar flex items-center justify-end ml-auto order-1 sm:order-2 -mt-3">
                                        <template x-if="createForm.payout_type === 'ball'">
                                            <div class="cssbv flex items-center space-x-2 text-sm text-gray-700">
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
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
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

                                        <template x-if="createForm.payout_type === 'coins'">
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('msg.one_token_equals') }}</span>
                                                <select name="payout_unit_value"
                                                    x-model="createForm.payout_unit_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([0.5, 1, 1.5, 2, 3, 5, 10, 20, 50, 100] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>

                                        <template x-if="createForm.payout_type === 'points'">
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
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
                                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                                <span>{{ __('msg.per_prize_value') }}</span>
                                                <select name="payout_unit_value" id="create_prize_value"
                                                    x-model="createForm.prize_value"
                                                    class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                                    @foreach ([1, 1.5, 2, 5, 10, 20, 30, 50, 100] as $value)
                                                        <option value="{{ $value }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span>{{ __('msg.currency_unit') }}</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div x-show="createForm.machine_category === 'pinball'"
                                    class="flex items-center space-x-2 text-sm text-gray-700 mb-4">
                                    <span>{{ __('msg.per_ball_value') }}</span>
                                    <select name="payout_unit_value" x-model="createForm.payout_unit_value"
                                        class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1">
                                        @foreach ([0.5, 1, 1.5, 2, 3, 5, 10, 20, 50, 100] as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <span>{{ __('msg.currency_unit') }}</span>
                                </div>

                                <div class="flex flex-col sm:justify-between sm:flex-row gap-4 order-3">
                                    <div class="css_credit flex items-center space-x-2 text-sm text-gray-700">
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
                                            class="rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-1"
                                            :disabled="createForm.payout_type === 'none' || !createForm.credit_in_enable"
                                            :class="{
                                                'opacity-50': createForm.payout_type === 'none' || !createForm
                                                    .credit_in_enable,
                                                'text-gray-700': createForm.credit_in_enable,
                                                'text-gray-400': !createForm.credit_in_enable
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

                                <div class=" py-5 flex flex-row space-x-2">
                                    <div class="mb-4 w-[50%]">
                                        <label for="create_revenue_split"
                                            class="block text-sm font-medium text-gray-700">
                                            {{ __('msg.revenue_split') }}% <span class="text-red-500">*</span>
                                        </label>
                                        <select name="revenue_split" id="create_revenue_split"
                                            x-model="createForm.revenue_split"
                                            class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            required>
                                            @foreach (range(5, 50, 5) as $value)
                                                <option value="{{ $value }}"
                                                    {{ $value == 45 ? 'selected' : '' }}>
                                                    {{ $value }}%
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('revenue_split')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mb-4 min-w-[40%]">
                                        <label for="create_share_pct" class="block text-sm font-medium text-gray-700">
                                            {{ __('msg.platform_machine_share_pct') }}% {{-- Assuming this new translation key --}}
                                        </label>
                                        <div class="mt-1 flex items-center">
                                            <select name="share_pct" id="create_share_pct"
                                                x-model="createForm.share_pct"
                                                class="block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">{{ __('msg.default') }} /
                                                    {{ __('msg.not_set') }}
                                                </option> {{-- Allows for NULL --}}


                                                @foreach (range(0, 10, 0.5) as $optionValue)
                                                    <option value="{{ $optionValue }}">{{ $optionValue }}%</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('share_pct')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <div x-show="createForm.machine_type === 'money_slot'"
                            class="border border-blue-300 p-4 rounded-md space-y-4 mt-4"> --}}
                        {{-- <h4 class="text-md font-semibold text-blue-700">
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
                            </div> --}}
                        <input type="hidden" name="accepted_denominations[]" value=""
                            x-show="createForm.machine_type !== 'money_slot'">

                        <div x-show="createForm.machine_category === 'utility'"
                            class="border border-blue-300 p-4 rounded-md space-y-4 mt-4">
                            <h4 class="text-md font-semibold text-blue-700">
                                {{ __('msg.money_slot') }} {{ __('msg.specific_settings') }}
                            </h4>
                            <div>
                                <label for="create_bill_currency_money_slot"
                                    class="block text-sm font-medium text-gray-700">
                                    {{ __('msg.bill_currency') }}
                                </label>
                                <select name="bill_currency" id="create_bill_currency_money_slot"
                                    x-model="createForm.bill_currency"
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach (config('bill_mappings', []) as $code => $displayNameKey)
                                        <option value="{{ $code }}">{{ __('msg.' . $code) }}
                                            ({{ __($code) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="createForm.bill_currency && available_denominations_for_selected_currency.length > 0"
                                class="mt-4">
                                <label
                                    class="block text-sm font-medium text-gray-700">{{ __('msg.accepted_denominations') }}</label>
                                <div class="px-3 mt-2 flex flex-wrap gap-x-4 gap-y-2 justify-between">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" x-model="createForm.all_denominations_selected"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span
                                            class="ml-2 text-sm font-medium text-gray-700">{{ __('msg.select_all') }}</span>
                                    </label>
                                    <template x-for="denomination in available_denominations_for_selected_currency"
                                        :key="denomination">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" :value="denomination"
                                                name="accepted_denominations[]"
                                                x-model="createForm.accepted_denominations"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600"
                                                x-text="formatNumberWithCommas(denomination)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                        {{-- </div> --}}
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
