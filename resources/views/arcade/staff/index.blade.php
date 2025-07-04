{{-- resources/views/arcade/index.blade.php --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
        <x-svg-icons name="list" classes="h-6 w-6 mr-2" />
        {{ __('msg.staff') }}{{ __('msg.management') }}
    </h2>
@endsection
@section('content')
    <div class="flex justify-center bg-gray-100" x-data="{ editStaffModal: false, selectedManager: {}, addStaffModal: false }">
        <div
            class="relative w-full bg-white  bg-opacity-60 shadow-lg rounded-lg">
            <div class="flex justify-end items-center mb-2 px-6 pt-2">
                <a class="px-3 "> {{ Auth::user()->invitation_code ?? __('msg.not_generated') }}</a>
                <x-button @click="addStaffModal = true; if (typeof hideLoadingOverlay === 'function') hideLoadingOverlay();"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.staff') }}
                </x-button>
            </div>
            <div class="m-auto mb-2">
                <!-- 員工列表 -->
                <div class="mx-auto max-w-lg"> <!-- 新增 mx-auto 使整個列表置中 -->
                    <!-- 表頭 -->
                    <div
                        class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg">
                        <div class="w-[25%] text-center">{{ __('msg.name') }}</div>
                        <div class="w-[40%] text-center">{{ __('msg.email') }}</div>
                        <div class="w-[35%] text-center">{{ __('msg.actions') }}</div>
                    </div>
                    <!-- 列表內容 -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                        @foreach ($staffs as $staff)
                            <div
                                class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                                <div class="w-[25%] break-words px-1 text-center">
                                    {{ $loop->iteration }}
                                    {{ $staff->name }}
                                </div>
                                <div class="w-[40%] break-words px-1 text-center">{{ $staff->email }}</div>
                                <div class="w-[35%] flex justify-center space-x-2">
                                    <!-- 啟用/停用按鈕 -->
                                    @php
                                        $actionText = $staff->is_active ? __('msg.deactivate') : __('msg.activate');
                                        $confirmMessage = __('msg.confirm_action', ['action' => $actionText]);
                                    @endphp
                                    <form action="{{ route('arcade.staff.deactivate', $staff) }}" method="POST"
                                        class="confirm-submit"
                                        data-confirm="{{ __('msg.confirm_action', ['action' => $actionText]) }}">
                                        @csrf
                                        <input type="hidden" name="is_active" value="{{ $staff->is_active ? 0 : 1 }}">
                                        <button type="submit" class="inline-block">
                                            @if ($staff->is_active)
                                                <x-svg-icons name="statusT" classes="h-6 w-6" />
                                            @else
                                                <x-svg-icons name="statusF" classes="h-6 w-6" />
                                            @endif
                                        </button>
                                    </form>

                                    <!-- 編輯按鈕 -->
                                    <button
                                        class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all duration-200"
                                        x-on:click="editStaffModal = true; selectedManager = { id: {{ $staff->id }}, name: '{{ $staff->name }}', email: '{{ $staff->email }}', sidebar_permissions: {{ json_encode($staff->sidebar_permissions) }} }">
                                        <x-svg-icons name="edit" classes="h-4 w-4" />
                                    </button>

                                    <!-- 刪除按鈕 -->
                                    <form action="{{ route('arcade.staff.destroy', $staff->id) }}" method="POST"
                                        class="confirm-submit" data-confirm="{{ __('msg.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-all duration-200">
                                            <x-svg-icons name="delete" classes="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 編輯管理員模態框 -->
        <x-modal.staff-edit :staffs="$staffs" />
        <x-modal.staff-create :staffs="$staffs" />

    </div>
@endsection
@php
    $title = __('msg.staff_management');
@endphp
