<!-- staff-create 模態框 -->
<div x-cloak x-show="addStaffModal" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black bg-opacity-50">
    </div>
    <div class="relative w-full h-full flex items-center justify-center p-4 ">
        <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
            @click.away="addStaffModal = false">
            <div class="absolute text-black right-0 top-0 m-4 p-2 cursor-pointer"
                @click="addStaffModal = false">X</div>
            <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.add') }}
                {{ __('msg.staff') }}</h2>
            <div class="p-2">
                <div class="sub-title text-sm font-bold text-black py-3">{{ __('msg.invitation_code') }}
                </div>
                <div class="text-xs text-black font-light"><a
                        class="text-red-500">@</a>{{ __('msg.invitation_code_description') }}</div>
                <div class="flex items-center space-x-2 py-2">
                    @php
                        $invitationRouteName = Auth::user()->hasRole('arcade-owner')
                            ? 'arcade.staff.generate.invitation'
                            : (Auth::user()->hasRole('machine-owner')
                                ? 'machine.staff.generate.invitation'
                                : '');
                        $storeRouteName = Auth::user()->hasRole('arcade-owner')
                            ? 'arcade.staff.store'
                            : (Auth::user()->hasRole('machine-owner')
                                ? 'machine.staff.store'
                                : '');
                        $staffRoleValue = Auth::user()->hasRole('arcade-owner')
                            ? 'arcade-staff'
                            : (Auth::user()->hasRole('machine-owner')
                                ? 'machine-staff'
                                : 'user'); // 預設一個基礎角色以防萬一
                    @endphp
                    <input type="text"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        value="{{ Auth::user()->invitation_code ?? __('msg.not_generated') }}" readonly>
                    <form method="POST"
                        action="{{ $invitationRouteName ? route($invitationRouteName, Auth::id()) : '#' }}"
                        data-confirm="{{ __('msg.invitation_code_confirm') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('msg.generate_new_code') }}
                        </button>
                    </form>
                </div>
            </div>
            <hr>

            <div class="p-2">
                <div class="sub-title text-sm font-bold text-black py-3">創建帳號</div>
                <form action="{{ $storeRouteName ? route($storeRouteName) : '#' }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-800">{{ __('msg.name') }}</label>
                        <input type="text" name="name"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            required>
                    </div>
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-800">{{ __('msg.default') }}{{ __('msg.email') }}
                            <div class="txtt-thin text-xs text-red-500">{{ __('msg.emailWarrning') }}</div>
                        </label>
                        <input type="email" name="email"
                            class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            required>
                    </div>
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-800">{{ __('msg.default') }}{{ __('msg.password') }}</label>
                        <input type="text" name="password"
                            class="mt-1 block w-full rounded-md shadow-sm border-0 cursor-default text-gray-400 bg-gray-300"
                            value="00000000" readonly autocomplete="password">
                    </div>
                    <input type="text" name="role" value="{{ $staffRoleValue }}" hidden>
                    <input type="hidden" name="filter_user_id" value="{{ Auth::id() }}">
                    <div class="flex justify-end mt-4">
                        <button type="button" class="px-4 py-2 mr-2 bg-gray-300 rounded-md hover:bg-gray-400"
                            @click="addStaffModal = false">取消</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">新增</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
