@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.user_management') }}
    </h2>
@endsection
@section('content')
    <div class="flex justify-center bg-gray-100" x-cloak x-data="{
        createUserModal: false,
        editUserModal: false,
        selectedUser: {},
        confirmDeactivate(userId, isActive) {
            if (confirm('Are you sure you want to deactivate this user?')) {
                console.log('Deactivating user:', userId);
            }
        }
    }">
        <div class="relative w-full bg-white bg-opacity-60 shadow-lg rounded-lg">
            <div class="flex justify-end items-center mb-2 px-6 pt-2">
                <x-button @click="createUserModal = true; $nextTick(() => checkRole('create'))"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.user') }}
                </x-button>
            </div>
            <div class="container mx-auto pb-3">
                <div class="bg-white rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div
                        class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg  text-center">
                        <div class="w-[4%] ">#</div>
                        <div class="w-[16%] text-center px-1">{{ __('msg.name') }}</div>
                        <div class="w-[40%] text-center">{{ __('msg.verify') }}/{{ __('msg.email') }}</div>
                        <div class="w-[16%] text-center min-h-full border-r-2 ">{{ __('msg.role') }}</div>
                        <div class="w-[9%] text-center">{{ __('msg.status') }}</div>
                        <div class="w-[15%] flex items-center justify-center ">{{ __('msg.actions') }}</div>
                    </div>
                    <!-- Data Rows -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                        @foreach ($users as $user)
                            <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 py-1">
                                <div class="w-[4%]  px-1 m-auto border-r text-xs ">{{ $user->id }}</div>
                                <div class="w-[16%] px-1 break-words m-auto text-center">
                                    <div class="font-thin w-full text-end pe-1" style="font-size: xx-small">
                                        <div class="font-thin"
                                            style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ optional($user->parent)->name }}</div>
                                    </div>
                                    {{ $user->name }}
                                </div>
                                <div class="flex w-[40%] px-1 break-words m-auto items-center justify-center"
                                    style="overflow-wrap: anywhere;">
                                    @if (!$user->email_verified_at)
                                        <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600 transition">
                                                {{ __('msg.verify') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs px-2 py-1 text-end rounded transition">
                                            <x-icon.verified />
                                        </span>
                                    @endif
                                    <span class="ps-2">
                                        {{ $user->email }}
                                    </span>
                                </div>
                                <!-- 帳戶類別 -->
                                <div class="w-[16%] min-h-full border-x px-1">{{ $user->roles->pluck('name')->join(', ') }}
                                </div>
                                <!-- 啟用/停用按鈕 -->
                                <div class="flex w-[9%] justify-center items-center m-auto">
                                    <form action="{{ route('admin.users.deactivate', $user) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?');">
                                        @csrf
                                        <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                        <button type="submit" class="inline-block">
                                            @if ($user->is_active)
                                                <x-svg-icons name="statusT" classes="h-6 w-6" />
                                            @else
                                                <x-svg-icons name="statusF" classes="h-6 w-6" />
                                            @endif
                                        </button>
                                    </form>
                                </div>
                                <div class="flex w-[15%] break-words justify-center items-center  m-auto space-x-1 ">
                                    {{-- <div class="flex justify-items-end space-x-1"> --}}
                                    <!-- 編輯按鈕 -->
                                    <x-button
                                        class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                        @click="editUserModal = true; selectedUser = {
                                            id: {{ $user->id }},
                                            name: '{{ $user->name }}',
                                            email: '{{ $user->email }}',
                                            role: '{{ optional($user->roles->first())->name }}',
                                            parent_name: '{{ optional($user->parent)->name }}'
                                        }; $nextTick(() => checkRole('edit'))">
                                        <x-svg-icons name="edit" classes="h-4 w-4" />
                                    </x-button>

                                    <!-- 刪除按鈕 -->
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                        class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <x-button
                                            class="bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto"
                                            type="submit"
                                            onclick="return confirm('{{ __('msg.confirm_delete') }}{{ __('msg.zh_ask') }}?')">
                                            <x-svg-icons name="delete" classes="h-4 w-4" />
                                        </x-button>
                                    </form>
                                    {{-- </div> --}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <x-modal.user_create-modal :roles="$roles" />
            <x-modal.user_edit-modal :roles="$roles" /> <!-- 傳遞 roles 變數 -->
        </div>
    </div>

    <script>
        function checkRole(modalType) {
            let selectRole, filter;
            if (modalType === 'create') {
                selectRole = document.querySelector('#selectRole');
                filter = document.querySelector('#filter');
            } else if (modalType === 'edit') {
                selectRole = document.querySelector('#selectRoleEdit');
                filter = document.querySelector('#filterEdit');
            }
            if (selectRole && filter) {
                filter.style.display = (selectRole.value === 'admin') ? 'none' : 'block';
                selectRole.addEventListener('change', function() {
                    filter.style.display = (selectRole.value === 'admin') ? 'none' : 'block';
                }, {
                    passive: true
                });
            }

        }
    </script>
@endsection
@php
    $title = __('msg.user_management');
@endphp
