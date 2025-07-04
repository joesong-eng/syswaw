{{-- /www/wwwroot/syswaw/resources/views/arcade/auth_keys/index.blade.php --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.auth_keys') }}
    </h2>
@endsection
@section('content')
    <div class="container mx-auto px-1 sm:px-6 lg:px-8 py-4 ">
        <div class="flex justify-end items-center mb-6 px-1">
            {{-- <h1 class="text-2xl font-semibold text-gray-900">驗證金鑰管理</h1> --}}
            {{-- 修改：新增金鑰按鈕，觸發 JS 確認 --}}
            {{-- <button type="button" onclick="confirmQuickAdd()"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-base text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="bi bi-plus-lg mr-1"></i> 新增金鑰
            </button> --}}
            <form action="{{ route('arcade.auth_keys.store') }}" method="POST" class="flex items-center confirm-submit"
                data-confirm="{{ __('msg.generate_key_confirm') }}">
                @csrf
                {{-- 數量選擇 --}}
                <select name="quantity" id="auth-key-add-quantity"
                    class="border rounded-md px-2 py-1 w-20 mr-2 text-sm">
                    <option value="1">1</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                </select>
                {{-- 擁有者選擇 (暫時只允許選擇自己) --}}
                {{-- 如果需要選擇其他用戶，這裡需要傳遞用戶列表並修改 Controller --}}
                <input type="hidden" name="owner_id" value="{{ Auth::id() }}">
                {{-- <select name="owner_id" id="auth-key-owner-id" class="border rounded-md px-2 py-1 mr-2 text-sm">
                    <option value="{{ Auth::id() }}" selected>{{ Auth::user()->name }} (自己)</option>
                    {{-- @foreach ($potentialOwners as $owner) <option value="{{ $owner->id }}">{{ $owner->name }}</option> @endforeach --}}
                {{-- </select> --}}
                <x-button>
                    {{ __('msg.add_auth_key') }}
                </x-button>
            </form>
            {{-- 結束修改 --}}
            {{-- 列印按鈕 --}}
            <button type="button" id="printButton"
                class="ml-2 px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-sm"
                onclick="handlePrintClick()">
                {{ __('msg.print') }}
            </button>
        </div>
        {{-- @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('info') }}</span>
            </div>
        @endif
        @if (session('success'))
            <div id="success-message"
                class="absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg  duration-1000 ease-out transform transition-transform slide-in">
                {{ session('success') }}</div>
        @endif --}}


        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="flex flex-col h-[calc(100vh-230px)] sm:h-[calc(100vh-152px)]">
                {{-- 使用類似 chip/index 的結構 --}}
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="bg-white rounded-lg shadow-lg">
                        <!-- Header Row -->
                        <div
                            class="flex items-center border-b border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            <div class="w-[4%] px-1 text-xs break-words items-center text-center">#</div>
                            <div class="w-[18%] px-1 text-base break-words items-center text-center">
                                <a href="{{ route('arcade.auth_keys.index', ['sort' => 'chip_hardware_id', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="hover:underline">
                                    {{ __('msg.chip_hardware_id') }}
                                    @if (request('sort') == 'chip_hardware_id')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[25%] px-1 text-base break-words items-center text-center">
                                <a href="{{ route('arcade.auth_keys.index', ['sort' => 'auth_key', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="hover:underline">
                                    {{ __('msg.token') }} (Auth Key)
                                    @if (request('sort') == 'auth_key')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[13%] px-1 text-base break-words items-center text-center">
                                <a href="{{ route('arcade.auth_keys.index', ['sort' => 'expires_at', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    {{-- Corrected route --}} class="hover:underline">
                                    {{ __('msg.expires_at') }}
                                    @if (request('sort') == 'expires_at')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[15%] px-1 text-base break-words items-center text-center">
                                <a href="{{ route('arcade.auth_keys.index', ['sort' => 'status', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    {{-- Corrected route --}} class="hover:underline">
                                    {{ __('msg.status') }}
                                    @if (request('sort') == 'status')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[10%] px-1 text-base break-words items-center text-center">
                                {{-- Creator sorting might need joining tables in controller --}}
                                {{ __('msg.creator') }}
                            </div>
                            <div class="w-[8%] text-base px-1 text-center">{{ __('msg.actions') }}</div>
                            <div class="w-[7%] px-1 break-words text-center">
                                <input type="checkbox" id="selectAllCheckbox" class="form-checkbox h-5 w-5 text-blue-600">
                                {{-- Select All Checkbox --}}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="DataRows flex-grow overflow-y-auto">
                    @forelse ($machineAuthKeys as $key)
                        <div
                            class="flex items-center border-b border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            <div id="chipKey" class="w-[4%] px-1 text-xs break-words items-center text-center">
                                {{ $key->id }}
                            </div>
                            <div id="authKey_{{ $key->id }}"
                                class="w-[25%] px-1 text-base break-words items-center text-center cursor-copy text-blue"
                                onclick="copyToClipboard('{{ $key->auth_key }}', 'authKey_{{ $key->id }}')"
                                title="{{ $key->auth_key }}">{{ $key->auth_key }}
                            </div>
                            <div class="w-[18%] px-2 py-4 break-words text-gray-500 text-center font-mono text-base"
                                title="{{ $key->chip_hardware_id }}">
                                {{ $key->chip_hardware_id ?? __('msg.not_set') }}
                            </div>
                            <div class="w-[13%] px-1 py-4 c-list break-words text-center text-gray-500">
                                {{ $key->expires_at ? $key->expires_at->format('ymd H:i') : '永不' }}
                            </div>
                            <div class="w-[15%] px-1 py-4 text-center break-words">
                                @if ($key->status === 'active')
                                    <span
                                        class="px-2 inline-flex text-base leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ __('msg.active') }}
                                    </span>
                                @elseif($key->status === 'inactive')
                                    <span
                                        class="px-2 inline-flex text-base leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('msg.inactive') }}
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-base leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ __('msg.pending') }}
                                    </span>
                                @endif
                            </div>
                            <div
                                class="w-[10%] px-1 py-4 whitespace-nowrap text-center text-xs break-words text-gray-500">
                                {{ $key->creator->name ?? __('msg.unknown_user') }}
                            </div>
                            <div class="w-[8%] px-1 py-4 whitespace-nowrap text-center font-medium">
                                {{-- 編輯按鈕 --}}
                                {{-- <a href="{{ route('arcade.auth_keys.edit', $key) }}" {{-- *** 編輯按鈕連結 *** --}}
                                {{-- class="text-indigo-600 hover:text-indigo-900 mr-1"
                                    title="編輯">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </a> --}}
                                {{-- 刪除按鈕 (使用表單提交) --}}
                                @if ($key->status == 'pending' && $key->machine_id == null)
                                    {{-- 只允許刪除 pending 且未綁定的 --}}
                                    <form action="{{ route('arcade.auth_keys.destroy', $key->id) }}" method="POST"
                                        class="inline-block confirm-delete"
                                        data-confirm="{{ __('msg.confirm_delete_key') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" {{-- 修改：如果已綁定機器，添加 disabled 屬性和樣式 --}}
                                            class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                            @if ($key->machine_id) disabled title="已綁定機器，無法刪除" @else title="刪除" @endif>
                                            <i class="bi bi-trash3"></i>
                                            {{-- 刪除 --}}
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="w-[7%] px-1 break-words text-center">
                                <input type="checkbox" name="selected_ids[]" value="{{ $key->id }}"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            目前沒有任何機器驗證金鑰。
                        </div>
                    @endforelse
                </div>
                {{-- </div> --}}
                <form id="lyml" action="">

                </form>

            </div>

            {{-- 新增：隱藏的表單，用於提交列印請求 --}}
            <form id="printForm" action="{{ route('arcade.auth_keys.print') }}" method="POST" style="display: none;">
                @csrf
                {{-- JavaScript 會在這裡動態添加 selected_ids[] input --}}
            </form>
            {{-- 分頁連結 --}}
            @if ($machineAuthKeys->hasPages())
                <div class="mt-4 px-4 py-2 bg-gray-50 border-t border-gray-200">
                    {{ $machineAuthKeys->appends(request()->query())->links() }} {{-- Pass current query params to pagination --}}
                </div>
            @endif

        </div>
    </div>


    {{-- 新增：JavaScript 確認函數 和 列印處理函數 --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Select All Checkbox functionality
                const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                const itemCheckboxes = document.querySelectorAll('input[name="selected_ids[]"]');

                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        itemCheckboxes.forEach(checkbox => {
                            checkbox.checked = selectAllCheckbox.checked;
                        });
                    });
                }

                itemCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (!checkbox.checked) {
                            selectAllCheckbox.checked = false;
                        } else {
                            // Check if all item checkboxes are checked
                            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                            selectAllCheckbox.checked = allChecked;
                        }
                    });
                });

                // Confirm Quick Add (already defined in layouts/app.js or here)
                // Ensure confirmQuickAdd is globally accessible if defined elsewhere, or define it here.
                // Example definition if not global:
                window.confirmQuickAdd = function() {
                    const quantitySelect = document.getElementById('auth-key-add-quantity');
                    if (!quantitySelect) {
                        alert('找不到數量選擇元素！');
                        return;
                    }
                    const quantity = quantitySelect.value;
                    const confirmationMessage = `確定要新增 ${quantity} 個金鑰嗎？\n(擁有者將設為您自己，狀態為待啟用)`;

                    if (confirm(confirmationMessage)) {
                        const form = document.getElementById('auth-key-add-form');
                        if (!form) {
                            console.error('無法找到表單元素!');
                            alert('頁面元素錯誤，無法提交請求。');
                            return;
                        }
                        if (typeof showLoadingOverlay === 'function') {
                            showLoadingOverlay();
                        }
                        form.submit();
                    } else {
                        console.log('Native confirm cancelled.');
                    }
                }

            }); // DOMContentLoaded 結束

            // handlePrintClick 函數已在 layouts/app.blade.php 中定義，這裡不需要重複定義
            // function handlePrintClick() { ... }
        </script>
    @endpush
@endsection

@php
    $title = __('msg.machine_auth_key_management');
@endphp
