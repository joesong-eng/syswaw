{{-- /www/wwwroot/syswaw/resources/views/admin/machine_auth_keys/edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('msg.edit') }}
                {{ __('msg.machine_auth_key_display_label') }}</h1>

            {{-- 顯示驗證錯誤 --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{-- 編輯表單 --}}
            <form action="{{ route('admin.machine_auth_keys.update', $machineAuthKey) }}" method="POST">
                @csrf
                @method('PUT') {{-- 或者 'PATCH' --}}

                {{-- 驗證金鑰 (通常不可編輯) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ __('msg.machine_auth_key_display_label') }}
                    </label>
                    <span
                        class="block w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 font-mono break-all">
                        {{ $machineAuthKey->auth_key }}
                    </span>
                    <p class="mt-1 text-sm text-gray-500">系統生成的驗證金鑰，通常不可修改。</p>
                </div>

                {{-- 晶片硬體 ID --}}
                <div class="mb-4">
                    <label for="chip_hardware_id" class="block text-sm font-medium text-gray-700">
                        {{ __('msg.chip_hardware_id') }}
                    </label>
                    <input type="text" name="chip_hardware_id" id="chip_hardware_id"
                        value="{{ old('chip_hardware_id', $machineAuthKey->chip_hardware_id) }}"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="{{ __('msg.chip_hardware_id_placeholder') }}">
                    <p class="mt-1 text-sm text-gray-500">由裝機人員提供或從設備讀取。</p>
                </div>

                {{-- 到期時間 --}}
                <div class="mb-4">
                    <label for="expires_at" class="block text-sm font-medium text-gray-700">
                        {{ __('msg.expires_at') }}
                    </label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                        value="{{ old('expires_at', $machineAuthKey->expires_at ? $machineAuthKey->expires_at->format('Y-m-d\TH:i') : '') }}"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <p class="mt-1 text-sm text-gray-500">留空表示永不過期。</p>
                </div>
                {{-- 擁有者 --}}
                <div class="mb-4">
                    <label for="owner_id" class="block text-sm font-medium text-gray-700">
                        {{ __('msg.owner') }} <span class="text-red-500">*</span> {{-- 標示為必填 --}}
                    </label>
                    {{-- 假設控制器傳遞了 $users 變數 --}}
                    <select name="owner_id" id="owner_id" required {{-- 添加 HTML5 必填驗證 --}}
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">-- {{ __('msg.select') }} {{ __('msg.owner') }} --</option>
                        {{-- *** 修改：遍歷 $owners 而不是 $users *** --}}
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}"
                                {{ old('owner_id', $machineAuthKey->owner_id) == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} ({{ $owner->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">選擇此金鑰的歸屬用戶。</p>
                </div>
                {{-- 狀態 --}}
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        狀態
                    </label>
                    <select name="status" id="status"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="pending"
                            {{ old('status', $machineAuthKey->status) == 'pending' ? 'selected' : '' }}>
                            {{ __('msg.pending') }}
                        </option>
                        <option value="active" {{ old('status', $machineAuthKey->status) == 'active' ? 'selected' : '' }}>
                            {{ __('msg.active') }}</option>
                        <option value="inactive"
                            {{ old('status', $machineAuthKey->status) == 'inactive' ? 'selected' : '' }}>
                            {{ __('msg.inactive') }}
                        </option>
                    </select>
                </div>

                {{-- 操作按鈕 --}}
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.machine_auth_keys.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                        {{ __('msg.cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        {{ __('msg.update') }}{{ __('msg.token') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
