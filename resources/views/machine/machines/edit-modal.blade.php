<div x-cloak class="fixed inset-0 z-50" x-show="editMachineModal" x-data="machineEditAlpineMachineContext"
    @keydown.escape="editMachineModal = false">
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="editMachineModal = false"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 bg-white w-full max-w-md rounded-lg shadow-lg"
            @click.away="editMachineModal = false">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.edit_machine') }}
                ({{ __('msg.machine_role') }})</h2>
            {{-- Machine Owner 更新機器的路由 --}}
            <form
                x-bind:action="selectedMachine && selectedMachine.id ?
                    '{{ route('machine.machines.update', ['machine' => '__MACHINE_ID__']) }}'.replace('__MACHINE_ID__',
                        selectedMachine.id) : '#'"
                method="POST">
                @csrf
                @method('PATCH')
                {{-- 錯誤訊息顯示 --}}
                <x-validation-errors class="mb-4" />

                <!-- Name -->
                <div class="mb-4">
                    <label for="name_edit_machine_context"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                    <input type="text" name="name" id="name_edit_machine_context"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-model="selectedMachine.name" required>
                    @error('name')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Machine Type -->
                <div class="mb-4">
                    <label for="machine_type_edit_machine_context"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.machine_type') }}</label>
                    <select id="machine_type_edit_machine_context" name="machine_type"
                        class="w-full mt-1 p-2 border rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-model="selectedMachine.machine_type" required>
                        <option value="pinball">{{ __('msg.pachinko') }}</option>
                        <option value="claw">{{ __('msg.claw_machine') }}</option>
                        <option value="points_redemption">{{ __('msg.machine_type_points_redemption') }}</option>
                        <option value="ticket_redemption">{{ __('msg.machine_type_ticket_redemption') }}</option>
                        <option value="normally">{{ __('msg.machine_type_general') }}</option>
                    </select>
                    @error('machine_type')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>


                <!-- Arcade/Location (Context dependent for Machine Owner) -->
                <div class="mb-4">
                    <label for="arcade_id_edit_machine_context"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.select_arcade') }}
                        / {{ __('msg.location') }}</label>
                    <select id="arcade_id_edit_machine_context" name="arcade_id"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-model="selectedMachine.arcade_id" required>
                        <option value="">{{ __('msg.select_arcade') }} / {{ __('msg.location') }}</option>
                        {{-- For Machine Owner, $arcades might be their list of managed locations/sites or specific arcades they operate in --}}
                        @foreach ($arcades ?? [] as $arcade)
                            {{-- Ensure $arcades is passed from controller --}}
                            <option value="{{ $arcade->id }}">{{ $arcade->name }}</option>
                        @endforeach
                    </select>
                    @error('arcade_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Auth Key (Display Only) -->
                <div class="mb-4">
                    <label for="chipkey_edit_display_machine_context"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.machine_auth_key_display_label') }}
                    </label>
                    <input type="text" id="chipkey_edit_display_machine_context"
                        class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        x-bind:value="selectedMachine.machine_auth_key?.auth_key || 'N/A'" readonly>
                    <p class="mt-1 text-sm text-gray-500">
                        <span
                            x-show="selectedMachine.machine_auth_key?.auth_key">{{ __('msg.auth_key_bound_hint') }}</span>
                        <span
                            x-show="!selectedMachine.machine_auth_key?.auth_key">{{ __('msg.auth_key_not_bound_hint') }}</span>
                    </p>
                </div>

                <!-- Chip Hardware ID -->
                <div class="mb-4">
                    <label for="edit_chip_hardware_id_modal_machine_context"
                        class="block text-sm font-medium text-gray-700">
                        {{ __('msg.chip_hardware_id') }}
                    </label>
                    <input type="text" name="chip_hardware_id" id="edit_chip_hardware_id_modal_machine_context"
                        x-model="selectedMachine.machine_auth_key.chip_hardware_id"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="{{ __('msg.chip_hardware_id_placeholder') }}">
                    @error('chip_hardware_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Conditional Override Fields based on selectedMachine.machine_type -->
                <div>
                    <div class="mt-4">
                        <label for="edit_credit_value_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.credit_value_per_token') }}
                            ({{ __('msg.optional') }})</label>
                        <input type="number" name="credit_value" id="edit_credit_value_machine_context" step="1"
                            min="0" x-model="selectedMachine.credit_value"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="10">
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('msg.empty_uses_default') }}</p>
                    </div>

                    <div class="mt-4" x-show="selectedMachine.machine_type === 'pinball'">
                        <label for="edit_balls_per_credit_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.balls_per_token_count') }}
                            ({{ __('msg.optional') }})</label>
                        <input type="number" name="balls_per_credit" id="edit_balls_per_credit_machine_context"
                            step="1" min="1" x-model="selectedMachine.balls_per_credit"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="10">
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('msg.pinball_only_empty_uses_default') }}</p>
                    </div>

                    <div class="mt-4"
                        x-show="selectedMachine.machine_type === 'points_redemption' || selectedMachine.machine_type === 'ticket_redemption'">
                        <label for="edit_points_per_credit_action_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.points_per_credit_action') }}
                            ({{ __('msg.optional') }})</label>
                        <input type="number" name="points_per_credit_action"
                            id="edit_points_per_credit_action_machine_context" step="1" min="1"
                            x-model="selectedMachine.points_per_credit_action"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="{{ __('msg.points_per_credit_action_placeholder') }}">
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('msg.points_per_credit_action_hint') }}</p>
                    </div>

                    <div class="mt-4"
                        x-show="selectedMachine.machine_type === 'points_redemption' || selectedMachine.machine_type === 'ticket_redemption' || selectedMachine.machine_type === 'claw'">
                        <label for="edit_payout_type_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.payout_type') }}
                            ({{ __('msg.optional') }})</label>
                        <select name="payout_type" id="edit_payout_type_machine_context"
                            x-model="selectedMachine.payout_type"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('msg.select') }}</option>
                            <option value="points">{{ __('msg.payout_type_points') }}</option>
                            <option value="tickets">{{ __('msg.payout_type_tickets') }}</option>
                            <option value="coins">{{ __('msg.payout_type_coins') }}</option>
                            <option value="prize">{{ __('msg.payout_type_prize_claw') }}</option>
                            <option value="none">{{ __('msg.payout_type_none') }}</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('msg.payout_type_hint') }}</p>
                    </div>

                    <div class="mt-4"
                        x-show="selectedMachine.machine_type === 'points_redemption' || selectedMachine.machine_type === 'ticket_redemption' || selectedMachine.machine_type === 'claw'">
                        <label for="edit_payout_unit_value_machine_context"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.payout_unit_value_rmb') }}
                            ({{ __('msg.optional') }})</label>
                        <input type="number" name="payout_unit_value" id="edit_payout_unit_value_machine_context"
                            step="1" min="0" x-model="selectedMachine.payout_unit_value"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="{{ __('msg.payout_unit_value_placeholder') }}">
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('msg.payout_unit_value_hint') }}</p>
                    </div>
                </div>

                <!-- Owner (Display Only for Machine Owner context, should be self) -->
                <div class="mb-4">
                    <label for="owner_id_edit_display_machine_context"
                        class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                    <span id="owner_id_edit_display_machine_context"
                        class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed p-2"
                        x-text="selectedMachine.owner?.name || 'N/A'"></span>
                    <input type="hidden" name="owner_id" x-model="selectedMachine.owner_id">
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" @click="editMachineModal = false"
                        class="px-4 py-2 bg-gray-300 rounded-md text-gray-700 hover:bg-gray-400">{{ __('msg.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">{{ __('msg.update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('machineEditAlpineMachineContext', () => ({
            // selectedMachine is passed from the main page's x-data
            init() {
                this.$watch('selectedMachine.machine_type', (newValue) => {
                    // console.log('Machine Role - Edit Modal - Machine type changed to:', newValue);
                });
            }
        }));
    });
</script>
