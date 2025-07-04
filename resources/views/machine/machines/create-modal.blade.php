<div x-cloak class="fixed inset-0 z-50" x-show="addMachineModal">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-1">
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
            @click.away="addMachineModal = false">

            <div class="bg-white p-3 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">
                    {{ __('msg.add') }}{{ __('msg.machine') }} ({{ __('msg.machine_role') }})</h2>
                {{-- Machine Owner 新增機器的路由 --}}
                <form action="{{ route('machine.machines.store') }}" method="POST">
                    @csrf
                    {{-- 錯誤訊息顯示 --}}
                    <x-validation-errors class="mb-4" />

                    <!-- Chip Key Input with Generate Button -->
                    <div class="mb-4">
                        <label for="chipKey_create_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.chip_token') }}</label>
                        <div class="relative" x-data="{
                            chipKey: '{{ old('chipKey') }}',
                            isLoading: false,
                            async generateAndFillKey() {
                                this.isLoading = true;
                                try {
                                    const response = await fetch(
                                        '{{ route('machine.auth_keys.generate_single') }}', { // Machine Owner 金鑰生成路由
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector(
                                                    'meta[name=\'csrf-token\']').content,
                                                'Accept': 'application/json',
                                            }
                                        });
                                    const data = await response.json();
                                    if (response.ok && data.auth_key) {
                                        this.chipKey = data.auth_key;
                                    } else {
                                        alert('{{ __('msg.failed_to_generate_key') }}' + (data.message ||
                                            '{{ __('msg.unknown_error') }}'));
                                    }
                                } catch (error) {
                                    console.error('{{ __('msg.generate_key_request_error') }}:', error);
                                    alert('{{ __('msg.generate_key_request_failed') }}: ' + error.message);
                                } finally {
                                    this.isLoading = false;
                                }
                            }
                        }">
                            <input type="text" name="chipKey" id="chipKey_create_machine_context" x-model="chipKey"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pr-24"
                                required placeholder="{{ __('msg.paste_chip_url_or_id') }}">
                            <button type="button" @click="generateAndFillKey()" :disabled="isLoading"
                                class="absolute top-0 right-0 h-full px-3 py-2 text-xs font-medium text-white bg-green-500 rounded-r-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                <span x-show="!isLoading">{{ __('msg.generate') }}</span>
                                <span x-show="isLoading">{{ __('msg.generating') }}...</span>
                            </button>
                        </div> {{-- End of relative div --}}
                        {{-- <p class="mt-1 text-sm text-gray-500">
                            {{ __('msg.paste_chip_url_or_id') }}
                        </p> --}}
                        @error('chipKey')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="create_chip_hardware_id_modal_machine_context"
                            class="block text-sm font-medium text-gray-700">
                            {{ __('msg.chip_hardware_id') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="chip_hardware_id" id="create_chip_hardware_id_modal_machine_context"
                            value="{{ old('chip_hardware_id') }}" required
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="{{ __('msg.chip_hardware_id_placeholder') }}">
                        @error('chip_hardware_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                        {{-- <p class="mt-1 text-sm text-gray-500">{{ __('msg.chip_hardware_id_hint') }}
                        </p> --}}
                    </div>

                    <div class="mb-4">
                        <label for="name_create_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                        <input type="text" name="name" id="name_create_machine_context"
                            value="{{ old('name') }}"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                        @error('name')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Machine Type and conditional fields -->
                    <div class="mb-4" x-data="{ currentMachineTypeMachineContext: '{{ old('machine_type', 'pinball') }}' }">
                        <label for="machine_type_create_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.machine_type') }}</label>
                        <select id="machine_type_create_machine_context" name="machine_type"
                            x-model="currentMachineTypeMachineContext"
                            class="w-full mt-1 p-2 border rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="pinball" @if (old('machine_type', 'pinball') == 'pinball') selected @endif>
                                {{ __('msg.pachinko') }}</option>
                            <option value="claw" @if (old('machine_type') == 'claw') selected @endif>
                                {{ __('msg.claw_machine') }}</option>
                            <option value="points_redemption" @if (old('machine_type') == 'points_redemption') selected @endif>
                                {{ __('msg.machine_type_points_redemption') }}
                            </option>
                            <option value="ticket_redemption" @if (old('machine_type') == 'ticket_redemption') selected @endif>
                                {{ __('msg.machine_type_ticket_redemption') }}
                            </option>
                            <option value="normally" @if (old('machine_type') == 'normally') selected @endif>
                                {{ __('msg.machine_type_general') }}</option>
                        </select>
                        @error('machine_type')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror

                        <div class="mt-4">
                            <label for="create_credit_value_machine_context"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.credit_value_per_token') }}
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="credit_value" id="create_credit_value_machine_context"
                                step="1" min="0" value="{{ old('credit_value') }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="10 ">
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('msg.empty_uses_default') }}</p>
                        </div>

                        <div class="mt-4" x-show="currentMachineTypeMachineContext === 'pinball'">
                            <label for="create_balls_per_credit_machine_context"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.balls_per_token_count') }}
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="balls_per_credit" id="create_balls_per_credit_machine_context"
                                step="1" min="1" value="{{ old('balls_per_credit') }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder=" 10 ">
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('msg.pinball_only_empty_uses_default') }}</p>
                        </div>

                        <div class="mt-4"
                            x-show="currentMachineTypeMachineContext === 'points_redemption' || currentMachineTypeMachineContext === 'ticket_redemption'">
                            <label for="create_points_per_credit_action_machine_context"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.points_per_credit_action') }}
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="points_per_credit_action"
                                id="create_points_per_credit_action_machine_context" step="1" min="1"
                                value="{{ old('points_per_credit_action') }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="{{ __('msg.points_per_credit_action_placeholder') }}">
                            {{-- <p class="mt-1 text-sm text-gray-500">
                                {{ __('msg.points_per_credit_action_hint') }}</p> --}}
                        </div>

                        <div class="mt-4"
                            x-show="currentMachineTypeMachineContext === 'points_redemption' || currentMachineTypeMachineContext === 'ticket_redemption' || currentMachineTypeMachineContext === 'claw'">
                            <label for="create_payout_type_machine_context"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.payout_type') }}
                                ({{ __('msg.optional') }})</label>
                            <select name="payout_type" id="create_payout_type_machine_context"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('msg.select') }}</option>
                                <option value="points" @if (old('payout_type') == 'points') selected @endif>
                                    {{ __('msg.payout_type_points') }}</option>
                                <option value="tickets" @if (old('payout_type') == 'tickets') selected @endif>
                                    {{ __('msg.payout_type_tickets') }}</option>
                                <option value="coins" @if (old('payout_type') == 'coins') selected @endif>
                                    {{ __('msg.payout_type_coins') }}</option>
                                <option value="prize" @if (old('payout_type') == 'prize') selected @endif>
                                    {{ __('msg.payout_type_prize_claw') }}
                                </option>
                                <option value="none" @if (old('payout_type') == 'none') selected @endif>
                                    {{ __('msg.payout_type_none') }}</option>
                            </select>
                            {{-- <p class="mt-1 text-sm text-gray-500">
                                {{ __('msg.payout_type_hint') }}</p> --}}
                        </div>

                        <div class="mt-4"
                            x-show="currentMachineTypeMachineContext === 'points_redemption' || currentMachineTypeMachineContext === 'ticket_redemption' || currentMachineTypeMachineContext === 'claw'">
                            <label for="create_payout_unit_value_machine_context"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.payout_unit_value_rmb') }}
                                ({{ __('msg.optional') }})</label>
                            <input type="number" name="payout_unit_value"
                                id="create_payout_unit_value_machine_context" step="1" min="0"
                                value="{{ old('payout_unit_value') }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="{{ __('msg.payout_unit_value_placeholder') }}">
                            {{-- <p class="mt-1 text-sm text-gray-500">
                                {{ __('msg.payout_unit_value_hint') }}</p> --}}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="arcade_id_create_machine_context"
                            class="text-gray-700">{{ __('msg.select_arcade') }}:</label>
                        <select id="arcade_id_create_machine_context" name="arcade_id"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                            <option value="">{{ __('msg.select_arcade') }} / {{ __('msg.location') }}</option>
                            {{-- For Machine Owner, $arcades might be their list of managed locations/sites or specific arcades they operate in --}}
                            @foreach ($arcades ?? [] as $arcade)
                                {{-- Ensure $arcades is passed from controller --}}
                                <option value="{{ $arcade->id }}"
                                    @if (old('arcade_id', count($arcades ?? []) === 1 ? $arcades[0]->id : null) == $arcade->id) selected @endif>
                                    {{ $arcade->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('arcade_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- For machine owner context, the owner_id is the authenticated machine owner --}}
                    <input type="hidden" name="owner_id" value="{{ Auth::id() }}">

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
