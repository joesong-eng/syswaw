<div x-cloak x-show="editModal" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="relative bg-white w-full max-w-md rounded-lg shadow-lg" @click.away="editModal = false">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Edit Role</h2>
            </div>
            <div class="p-6">
                <form :action="`/admin/roles/${selectedRole.id}`" method="POST">
                    {{-- <form :action="`/admin/roles/update/${selectedRole.id}`" method="POST"> --}}
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="edit-name"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                        <input type="text" id="edit-name" name="name" x-model="selectedRole.name" required
                            class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300 transition">
                    </div>
                    <div class="mb-4">
                        <label for="edit-level"
                            class="block text-sm font-medium text-gray-700">{{ __('msg.level') }}</label>
                        <input type="text" id="edit-level" name="level" x-model="selectedRole.level" required
                            class="mt-1 block w-full px-4 py-2 bg-gray-50 border m-auto border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300 transition">
                    </div>
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" @click="editModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">{{ __('msg.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 transition">{{ __('msg.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
