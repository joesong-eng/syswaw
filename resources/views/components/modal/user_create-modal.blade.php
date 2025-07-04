<div x-cloak x-show="createUserModal" class="fixed inset-0 z-50" x-data="{
    filterText: '',
    selectedRole: '',
    users: [],
    fetchUsers() {
        if (this.filterText.length === 0) {
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
        this.$refs.parentId.value = user.id; // 設置 parent_id
    },
    updateSelectedRole() {
        this.selectedRole = this.$refs.selectRole.value; // 更新所選角色
        this.fetchUsers(); // 當角色改變時重新篩選
    },
    init() {
        this.filterText = '';
        this.selectedRole = this.$refs.selectRole ? this.$refs.selectRole.value : '';
        this.users = [];
        this.$refs.parentId.value = '';
    }
}">
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="createUserModal = false"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('msg.add') }}{{ __('msg.user') }}</h2>
            <form action="{{ route('admin.users.store') }}" method="POST" @submit="createUserModal = false">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-800">{{ __('msg.name') }}</label>
                    <input type="text" name="name" autocomplete="username"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-800">{{ __('msg.email') }}</label>
                    <input type="email" name="email" autocomplete="email"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-800">{{ __('msg.password') }}</label>
                    <input type="password" name="password" autocomplete="new-password"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-800">{{ __('msg.role') }}</label>
                    <select id="selectRole" name="role" x-ref="selectRole"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        @change="updateSelectedRole">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="filter" class="mb-4 relative">
                    <label class="block text-sm font-medium text-gray-800">{{ __('msg.select_parent_user') }}
                        <div class="inline-block text-gray-500 font-thin text-xs">**{{ __('msg.text_filter') }}**</div>
                    </label>
                    <input id="filterUser" type="text" name="filterUser" autocomplete="off"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        x-model="filterText" @input.debounce.300ms="fetchUsers"
                        placeholder="{{ __('msg.enter_username_to_filter') }}">
                    <input type="hidden" name="parent_id" x-ref="parentId">
                    <div id="listUserEdit"
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg  max-w-48"
                        style="max-height: 140px; overflow-y: auto;" x-show="users.length > 0" x-cloak>
                        <template x-for="user in users" :key="user.id">
                            <div class="px-3 py-2 text-sm text-gray-800 hover:bg-gray-100 cursor-pointer"
                                @click="selectUser(user)" x-text="user.name">
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex justify-end mt-4 space-x-2">
                    <button type="button" @click="createUserModal = false"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">
                        {{ __('msg.cancel') }}
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                        {{ __('msg.create_user') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
