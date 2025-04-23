@extends('layouts.app')
@section('content')
    <div class="py-1">
        <div class=" max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="relative bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- User Stats -->
                {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> --}}
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                        <div class="text-right">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                註冊時間: {{ Auth::user()->created_at->format('Y-m-d') }}
                            </dt>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                帳戶狀態:{{ Auth::user()->is_active ? '活躍' : '停用' }}
                            </dt>
                        </div>
                    </div>
                {{-- </div> --}}
                <div class="flex gap-1">
                    <!-- Stats Card 1 -->
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-3">
                        <div class="text-center">
                            <h2 class="text-gray-600 dark:text-gray-300 text-sm font-medium">總用戶數</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ \App\Models\User::count() }}</p>
                        </div>
                    </div>
                
                    <!-- Stats Card 2 -->
                    {{-- <div class="flex-1 bg-white dark:bg-gray-700 rounded-lg shadow p-3"> --}}
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 dark:text-gray-300 text-sm font-medium">活躍用戶</h2>
                                <p class="text-2xl text-center font-semibold text-gray-800 dark:text-gray-100">{{ \App\Models\User::where('is_active', true)->count() }}</p>
                            </div>
                        </div>
                    {{-- </div> --}}
                
                    <!-- Stats Card 3 -->
                    {{-- <div class="flex-1 bg-white dark:bg-gray-700 rounded-lg shadow p-3"> --}}
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 dark:text-gray-300 text-sm font-medium">角色總數</h2>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ \App\Models\Role::count() }}</p>
                            </div>
                        </div>
                    {{-- </div> --}}
                </div>
                {{-- @if (Auth::user()->hasRole('admin'))
                角色{{ Auth::user()->roles->first()->name }}
                @endif --}}
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">快速操作</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-modal.sidebar-link 
                            route="admin.users" icon="user" 
                            title="{{ __('msg.user_management') }}" 
                            description="{{ __('msg.user_management_desc') }}" 
                        />
                    {{-- <a href="{{ route('users.index') }}" class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <div class="ml-4">
                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">用戶管理</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">管理系統用戶和權限</p>
                            </div>
                        </a> --}}
                        <x-modal.sidebar-link 
                            route="roles.index" icon="role" 
                            title="{{ __('msg.role_management') }}" 
                            description="{{ __('msg.role_management_desc') }}" 
                        />
                        {{-- <a href="{{ route('roles.index') }}" class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <div class="ml-4">
                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">角色管理</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">增刪改角色</p>
                            </div>
                        </a> --}}

                        <x-modal.sidebar-link 
                            route="admin.tcp-server" icon="transactions" 
                            title="{{ __('msg.data_stream') }}" 
                            description="{{ __('msg.data_stream_desc') }}" 
                        />
                    {{-- <x-svg-icons name="transactions" classes="h-6 w-6" /> --}}
                        {{-- <a href="{{ route('admin.tcp.service') }}" class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-600">
                            <div class="ml-4">
                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ __('msg.data_stream') }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('msg.data_stream_desc') }}</p>
                            </div>
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@php
    $title = 'Dashboard.admin';
@endphp