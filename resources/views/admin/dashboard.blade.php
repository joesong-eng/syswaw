@extends('layouts.app')
@section('content')
    <div class="py-1">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- User Stats -->
                <div class="bg-white overflow-hidden shadow rounded-lg p-2">
                    <div class="text-right">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            註冊時間: {{ Auth::user()->created_at->format('Y-m-d') }}
                        </dt>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            帳戶狀態:{{ Auth::user()->is_active ? '活躍' : '停用' }}
                        </dt>
                    </div>
                </div>

                <div class="flex gap-1">
                    <!-- Stats Card 1 -->
                    <div class="bg-white rounded-lg shadow p-3">
                        <div class="text-center">
                            <h2 class="text-gray-600 text-sm font-medium">總用戶數</h2>
                            <p class="text-2xl font-semibold text-gray-800">{{ \App\Models\User::count() }}
                            </p>
                        </div>
                    </div>

                    <!-- Stats Card 2 -->
                    <div class="bg-white rounded-lg shadow p-3">
                        <div class="text-center">
                            <h2 class="text-gray-600 text-sm font-medium">活躍用戶</h2>
                            <p class="text-2xl text-center font-semibold text-gray-800">
                                {{ \App\Models\User::where('is_active', true)->count() }}</p>
                        </div>
                    </div>

                    <!-- Stats Card 3 -->
                    <div class="bg-white rounded-lg shadow p-3">
                        <div class="text-center">
                            <h2 class="text-gray-600 text-sm font-medium">角色總數</h2>
                            <p class="text-2xl font-semibold text-gray-800">{{ \App\Models\Role::count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900">快速操作</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-modal.sidebar-link route="admin.users.index" icon="user"
                            title="{{ __('msg.user_management') }}" description="{{ __('msg.user_management_desc') }}" />

                        <x-modal.sidebar-link route="admin.roles.index" icon="role"
                            title="{{ __('msg.role_management') }}" description="{{ __('msg.role_management_desc') }}" />

                        <x-modal.sidebar-link route="admin.tcp-server.index" icon="transactions"
                            title="{{ __('msg.data_stream') }}" description="{{ __('msg.data_stream_desc') }}" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@php
    $title = 'Dashboard.admin';
@endphp
