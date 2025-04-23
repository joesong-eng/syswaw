{{-- resources/views/arcade/staff.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900" 
    x-data="{ editStaffModal: false, selectedManager: {}, addStaffModal: false }">
    <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <!-- Add New Manager 標題和按鈕 -->
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <div name="elem1" class="flex items-start w-1/2">
                    <x-svg-icons name="list" classes="h-6 w-6" />
                    <span class="text-lg font-thin">{{ __('msg.staff') }}{{ __('msg.management') }}</span>
                </div>
                <button @click="addStaffModal = true" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.staff') }}
                </button>
            </div>
            <div class="m-auto mb-2">
                <!-- 員工列表 -->
                <div class="mx-auto max-w-lg"> <!-- 新增 mx-auto 使整個列表置中 -->
                    <!-- 表頭 -->
                    <div class="flex items-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg">
                        <div class="w-[25%] text-center">{{ __('msg.name') }}</div>
                        <div class="w-[40%] text-center">{{ __('msg.email') }}</div>
                        <div class="w-[35%] text-center">{{ __('msg.actions') }}</div>
                    </div>
                    <!-- 列表內容 -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                        @foreach ($staffs as $staff)
                            <div class="flex items-center border-b border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-100 p-2">
                                <div class="w-[25%] break-words px-1 text-center">
                                    {{ $loop->iteration }}
                                    {{ $staff->name }}
                                </div>
                                <div class="w-[40%] break-words px-1 text-center">{{ $staff->email }}</div>
                                <div class="w-[35%] flex justify-center space-x-2">
                                    <!-- 啟用/停用按鈕 -->
                                    <form action="{{ route('staff.deactivate', $staff) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure?')">
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
                                    <button class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all duration-200"
                                            x-on:click="editStaffModal = true; selectedManager = { id: {{ $staff->id }}, name: '{{ $staff->name }}', email: '{{ $staff->email }}', sidebar_permissions: {{ json_encode($staff->sidebar_permissions) }} }">
                                        <x-svg-icons name="edit" classes="h-4 w-4" />
                                    </button>
                                    {{-- {{ dump(json_encode($staff->sidebar_permissions)) }} --}}

                                    {{-- <button class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all duration-200 ease-in-out"
                                        @click="editStaffModal = true; selectedManager = { id: {{ $staff->id }}, name: '{{ $staff->name }}', email: '{{ $staff->email }}', sidebar_permissions: {{ json_encode($staff->sidebar_permissions) }} }; console.log(selectedManager)">
                                        <x-svg-icons name="edit" classes="h-4 w-4" />
                                    </button> --}}
                                    
                                    <!-- 刪除按鈕 -->
                                    <form action="{{ route('staff.destroy', $staff->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-all duration-200"
                                                onclick="return confirm('Are you sure?')">
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