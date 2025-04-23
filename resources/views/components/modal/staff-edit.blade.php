<!-- resources/views/components/modal/staff-edit.blade.php -->
<div x-cloak x-show="editStaffModal" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 relative bg-white dark:bg-gray-800 w-full max-w-md rounded-lg shadow-lg" @click.away="editStaffModal = false">
            <div class="absolute text-black dark:text-white right-0 top-0 m-4 p-2 cursor-pointer" @click="editStaffModal = false">X</div>
            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">{{__('msg.edit')}}{{__('msg.staff')}}</h2>
            <div class="p-2"> 
                <form :action="`staff/update/${selectedManager.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <label class="block text-md font-medium text-gray-800 p-3 rounded-t-lg bg-yellow-200"
                    >{{ __('msg.change') }} {{ __('msg.name') }}/{{ __('msg.password') }}</label>
                    <div class="mb-4 border p-4 rounded-b-lg">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-100">{{__('msg.email')}}</label>
                            <div x-text="selectedManager.email"></div>
                            {{-- <input type="email" x-model="selectedManager.email" name="email" 
                                   class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed" 
                                   readonly required> --}}
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-100">{{__('msg.name')}}</label>
                            <input type="text" x-model="selectedManager.name" name="name" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-100">{{__('msg.password')}} ({{ __('msg.Optional') }})</label>
                            <input type="password" x-model="selectedManager.password" name="password" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" autocomplete="password">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-100">{{__('msg.confirm')}} {{__('msg.password')}}</label>
                            <input type="password" x-model="selectedManager.password_confirmation" name="password_confirmation" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" autocomplete="password">
                        </div>
                    </div>

                    <label class="block text-md font-medium text-gray-800 p-3 rounded-t-lg bg-yellow-200"
                    >{{ __('msg.sidebar_permissions') }}</label>
                    <div class="mb-4 border p-4 rounded-b-lg">
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="chips.index"
                                       x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes('chips.index')"
                                       class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('msg.chip_token') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="machine.index"
                                       x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes('machine.index')"
                                       class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('msg.machine_management') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="add_machine_owner"
                                       x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes('add_machine_owner')"
                                       class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('msg.add_machine_owner') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="sidebar_permissions[]" value="transactions.index"
                                       x-bind:checked="selectedManager.sidebar_permissions && selectedManager.sidebar_permissions.includes('transactions.index')"
                                       class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('msg.transaction_management') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button" class="px-4 py-2 mr-2 bg-gray-300 rounded-md hover:bg-gray-400" @click="editStaffModal = false">{{__('msg.cancel')}}</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">{{__('msg.save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>