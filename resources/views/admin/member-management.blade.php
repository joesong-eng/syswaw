@extends('layouts.app')
@section('content')
    <div class="py-1">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- 只有 admin 和 admin經理 能訪問 -->
                @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('admin_manager'))
                    <div class="text-right mb-4">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            註冊時間: {{ Auth::user()->created_at->format('Y-m-d') }}
                        </dt>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            帳戶狀態: {{ Auth::user()->is_active ? '活躍' : '停用' }}
                        </dt>
                    </div>

                    <!-- 統計數據卡片 -->
                    <div class="flex gap-1 mb-6">
                        <!-- 總機器數 -->
                        <div class="bg-white rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 text-sm font-medium">總機器數</h2>
                                <p class="text-2xl font-semibold text-gray-800">
                                    {{ \App\Models\Machine::count() }}</p>
                            </div>
                        </div>
                        <!-- 總商鋪數 -->
                        <div class="bg-white rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 text-sm font-medium">總商鋪數</h2>
                                <p class="text-2xl font-semibold text-gray-800">
                                    {{ \App\Models\Store::count() }}</p>
                            </div>
                        </div>
                        <!-- 總會員數 -->
                        <div class="bg-white rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 text-sm font-medium">總會員數</h2>
                                <p class="text-2xl font-semibold text-gray-800">
                                    {{ \App\Models\Member::count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 新增商鋪表單 -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-medium text-gray-800 mb-4">新增商鋪</h2>

                        <form action="{{ route('stores.store') }}" method="POST">
                            @csrf

                            <!-- 商鋪名稱 -->
                            <div class="mb-4">
                                <label for="aracde_name"
                                    class="block text-sm font-medium text-gray-700">商鋪名稱</label>
                                <input type="text" id="aracde_name" name="aracde_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required>
                            </div>

                            <!-- 用戶選擇 -->
                            <div class="mb-4">
                                <label for="user_id"
                                    class="block text-sm font-medium text-gray-700">選擇店主用戶</label>
                                <select id="user_id" name="user_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">選擇現有用戶</option>
                                    @foreach (\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 或者新增新用戶 -->
                            <div class="mb-4">
                                <label for="new_user_name"
                                    class="block text-sm font-medium text-gray-700">新用戶名稱</label>
                                <input type="text" id="new_user_name" name="new_user_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="mb-4">
                                <label for="new_user_email"
                                    class="block text-sm font-medium text-gray-700">新用戶電子郵件</label>
                                <input type="email" id="new_user_email" name="new_user_email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none">新增商鋪</button>
                            </div>
                        </form>
                    </div>
                @else
                    <p class="text-red-500">您無權訪問此頁面。</p>
                @endif
            </div>
        </div>
    </div>
@endsection
@php
    $title = '';
@endphp
