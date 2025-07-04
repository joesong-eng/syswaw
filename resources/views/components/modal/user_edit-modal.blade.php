<!-- resources/views/components/modal/user_edit-modal.blade.php -->
<div x-cloak x-show="editUserModal" class="fixed inset-0 z-50" x-data="{
    filterText: '',
    selectedRole: '',
    parent: '',
    users: [],
    fetchUsers() {
        if (this.filterText.length === 0 || !this.selectedRole) {
            this.users = [];
            return;
        }
        fetch('{{ route('admin.users.search') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    query: this.filterText,
                    role: this.selectedRole
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                this.users = Array.isArray(data.users) ? data.users : [];
            })
            .catch(error => {
                console.error('Error fetching users:', error);
                this.users = [];
            });
    },
    selectUser(user) {
        this.filterText = user.name;
        this.users = [];
        this.$refs.parentId.value = user.id; // 將選擇的 user.id 存入隱藏欄位
    },
    updateSelectedRole() {
        this.selectedRole = this.$refs.selectRoleEdit.value; // 更新所選角色
        this.fetchUsers(); // 當角色改變時重新篩選
    },
    init() {
        this.filterText = selectedUser.parent_name || '';
        this.parent = selectedUser.parent_name || '';
        this.selectedRole = selectedUser.role || '';
        this.$refs.parentId.value = selectedUser.parent_id || '';
        this.users = [];
        this.selectedUser.sidebar_permissions = selectedUser.sidebar_permissions || []; // 確保 sidebar_permissions 是陣列
    }
}" x-init="$watch('editUserModal', value => {
    if (value) {
        filterText = selectedUser.parent_name || '';
        parent = selectedUser.parent_name || '';
        selectedRole = selectedUser.role || '';
        $refs.parentId.value = selectedUser.parent_id || '';
        selectedUser.sidebar_permissions = selectedUser.sidebar_permissions || []; // 確保 sidebar_permissions 是陣列
        fetchUsers(); // 可選：初始化時觸發篩選
    }
})">
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="editUserModal = false"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.edit') }}
                {{ __('msg.user') }}</h2>
            <div class="p-2">
                <form :action="'/admin/user/update/' + selectedUser.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-800">{{ __('msg.name') }}</label>
                        <input type="text" x-model="selectedUser.name" name="name"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-800">{{ __('msg.email') }}</label>
                        <input type="email" x-model="selectedUser.email" name="email"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-800">{{ __('msg.role') }}</label>
                        <select id="selectRoleEdit" x-model="selectedUser.role" name="role" x-ref="selectRoleEdit"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            @change="updateSelectedRole">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <template x-if="selectedUser.role === 'arcade-staff'">
                        <div class="mb-4 border p-4 rounded">
                            <label
                                class="block text-sm font-medium text-gray-800 mb-2">{{ __('msg.sidebar_permissions') }}</label>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="sidebar_permissions[]" value="chips.index"
                                        x-bind:checked="selectedUser.sidebar_permissions && selectedUser.sidebar_permissions.includes(
                                            'chips.index')"
                                        class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-gray-700">{{ __('msg.chip_token') }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="sidebar_permissions[]" value="machine.index"
                                        x-bind:checked="selectedUser.sidebar_permissions && selectedUser.sidebar_permissions.includes(
                                            'machine.index')"
                                        class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-gray-700">{{ __('msg.machine_management') }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="sidebar_permissions[]" value="add_machine_owner"
                                        x-bind:checked="selectedUser.sidebar_permissions && selectedUser.sidebar_permissions.includes(
                                            'add_machine_owner')"
                                        class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-gray-700">{{ __('msg.add_machine_owner') }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="sidebar_permissions[]" value="transactions.index"
                                        x-bind:checked="selectedUser.sidebar_permissions && selectedUser.sidebar_permissions.includes(
                                            'transactions.index')"
                                        class="form-checkbox h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-gray-700">{{ __('msg.transaction_management') }}</span>
                                </label>
                            </div>
                        </div>
                    </template>

                    <div id="filterEdit" class="mb-4 relative">
                        <div class="flex justify-between items-center mb-2">
                            <label id="l1" class="block text-sm font-medium text-gray-800">
                                {{ __('msg.assign_parent_user') }}
                            </label>
                            <div id="r1" class="flex items-center justify-end max-w-[66.66%]">
                                <span class="text-sm text-gray-600 whitespace-nowrap mr-2">
                                    {{ __('msg.previous_parent') }}:
                                </span>
                                <div x-text="parent"
                                    class="border-0 p-0 text-purple-400 font-extrabold text-lg cursor-default bg-transparent overflow-x-auto"
                                    style="width: max-content; min-width: 0; max-width: 100%; display: inline-block;">
                                </div>
                            </div>
                        </div>
                        <input id="filterUserEdit" type="text" name="filterUser"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            x-model="filterText" @input.debounce.300ms="fetchUsers"
                            placeholder="{{ __('msg.enter_username_to_filter') }}" autocomplete="off">
                        <input type="hidden" name="parent_id" x-ref="parentId">
                        <div id="listUserEdit"
                            class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-w-48"
                            style="max-height: 150px; overflow-y: auto;" x-show="users.length > 0" x-cloak>
                            <template x-for="user in users" :key="user.id">
                                <div class="px-3 py-2 text-sm text-gray-800 hover:bg-gray-100 cursor-pointer"
                                    @click="selectUser(user)" x-text="user.name">
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4 space-x-2">
                        <button type="button"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition"
                            @click="editUserModal = false">{{ __('msg.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                            {{ __('msg.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
