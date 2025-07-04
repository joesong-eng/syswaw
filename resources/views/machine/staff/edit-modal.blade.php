<!-- resources/views/machine/staff/edit-modal.blade.php -->
<div x-cloak x-show="editStaffModal" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
            @click.away="editStaffModal = false">
            <div class="absolute text-black right-0 top-0 m-4 p-2 cursor-pointer"
                @click="editStaffModal = false">X</div>
            <h2 class="text-lg font-semibold mb-4 text-gray-800">
                {{ __('msg.edit') }}{{ __('msg.staff') }} ({{ __('msg.machine_role') }})
            </h2>
            <div class="p-2">
                @php
                    // This dynamic prefix will correctly use 'machine/staff' when a machine-owner is logged in.
                    $updateRoutePrefix = Auth::user()->hasRole('arcade-owner')
                        ? 'arcade/staff'
                        : (Auth::user()->hasRole('machine-owner')
                            ? 'machine/staff'
                            : '');
                @endphp
                <form x-bind:action="`{{ $updateRoutePrefix ? url($updateRoutePrefix) : '' }}/${selectedManager.id}`"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <label
                        class="block text-md font-medium text-gray-800 p-3 rounded-t-lg bg-yellow-200">{{ __('msg.change') }}
                        {{ __('msg.name') }}/{{ __('msg.password') }}</label>
                    <div class="mb-4 border p-4 rounded-b-lg">

                        <div class="mb-4">
                            <label for="email_machine_staff_edit"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.email') }}</label>
                            <input type="email" name="email" id="email_machine_staff_edit"
                                class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed"
                                x-model="selectedManager.email" readonly>
                        </div>
                        <div class="mb-4">
                            <label for="name_machine_staff_edit"
                                class="block text-sm font-medium text-gray-800">{{ __('msg.name') }}</label>
                            <input type="text" x-model="selectedManager.name" name="name"
                                id="name_machine_staff_edit"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="password_machine_staff_edit"
                                class="block text-sm font-medium text-gray-800">{{ __('msg.password') }}
                                ({{ __('msg.Optional') }})</label>
                            <input type="password" x-model="selectedManager.password" name="password"
                                id="password_machine_staff_edit"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                autocomplete="new-password">
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation_machine_staff_edit"
                                class="block text-sm font-medium text-gray-800">{{ __('msg.confirm') }}
                                {{ __('msg.password') }}</label>
                            <input type="password" x-model="selectedManager.password_confirmation"
                                name="password_confirmation" id="password_confirmation_machine_staff_edit"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                autocomplete="new-password">
                        </div>
                    </div>

                    <label
                        class="block text-md font-medium text-gray-800 p-3 rounded-t-lg bg-yellow-200">{{ __('msg.sidebar_permissions') }}</label>
                    <div class="mb-4 border p-4 rounded-b-lg">
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="machine.auth_keys.index"
                                    x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes(
                                        'machine.auth_keys.index')"
                                    class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-gray-700">{{ __('msg.chip_token') }}
                                    {{ __('msg.management') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="machine.machines.index"
                                    x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes(
                                        'machine.machines.index')"
                                    class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span
                                    class="ml-2 text-gray-700">{{ __('msg.machine_management') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="machine.transactions.index"
                                    x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes(
                                        'machine.transactions.index')"
                                    class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span
                                    class="ml-2 text-gray-700">{{ __('msg.transaction_management') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button" class="px-4 py-2 mr-2 bg-gray-300 rounded-md hover:bg-gray-400"
                            @click="editStaffModal = false">{{ __('msg.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">{{ __('msg.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
