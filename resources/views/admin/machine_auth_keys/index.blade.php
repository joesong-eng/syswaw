{{-- /www/wwwroot/syswaw/resources/views/admin/machine_auth_keys/index.blade.php --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.verify') }}{{ __('msg.token_management') }}
    </h2>
@endsection
@section('content')
    <div class="flex justify-end items-center mb-2 px-6 pt-2">
        <form id="auth-key-add-form" action="{{ route('admin.machine_auth_keys.store') }}" method="POST"
            class="flex items-center">
            @csrf
            {{-- 一次要列印幾張 --}}
            <select name="quantity" id="auth-key-add-quantity" class="border rounded-md px-2 py-1 w-20 mr-2 text-sm">
                <option value="1">1</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
                <option value="50">50</option>
            </select>
            <x-button type="button" onclick="confirmQuickAdd()">
                {{ __('msg.add_auth_key') }}
            </x-button>
        </form>
    </div>
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    @if (session('info'))
        {{-- Added for info messages like redirection from create --}}
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif
    <div class="container mx-auto px-1 sm:px-6 lg:px-8 py-1 ">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="flex flex-col h-[calc(100vh-230px)] sm:h-[calc(100vh-152px)]">
                {{-- 使用類似 chip/index 的結構 --}}
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="bg-white rounded-lg shadow-lg">
                        <!-- Header Row -->
                        <div class="flex items-center border-b border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            <div class="w-[4%] text-xs break-words items-center text-center">#</div>
                            <div class="w-[21%] text-xs break-words items-center text-center">
                                <a href="{{ route('admin.machine_auth_keys.index', ['sort' => 'auth_key', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="hover:underline">
                                    {{ __('msg.token') }} (Auth Key)
                                    @if (request('sort') == 'auth_key')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[20%] text-xs break-words items-center text-center">
                                <a href="{{ route('admin.machine_auth_keys.index', ['sort' => 'chip_hardware_id', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="hover:underline">
                                    {{ __('msg.chip_hardware_id') }}
                                    @if (request('sort') == 'chip_hardware_id')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[15%] text-xs break-words items-center text-center">
                                <a href="{{ route('admin.machine_auth_keys.index', ['sort' => 'expires_at', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="hover:underline">
                                    {{ __('msg.expires_at') }}
                                    @if (request('sort') == 'expires_at')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[17%] text-xs break-words items-center text-center">
                                <a href="{{ route('admin.machine_auth_keys.index', ['sort' => 'status', 'direction' => request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="hover:underline">
                                    {{ __('msg.status') }}
                                    @if (request('sort') == 'status')
                                        <i
                                            class="bi bi-arrow-{{ request('direction', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                    @endif
                                </a>
                            </div>
                            <div class="w-[13%] text-xs break-words items-center text-center">
                                {{-- Creator sorting might need joining tables in controller --}}
                                {{ __('msg.creator') }}
                            </div>
                            <div class="w-[10%] text-xs px-1 text-center">{{ __('msg.actions') }}</div>
                            <button type="button" id="printButton"
                                class="w-[9%] qrcodeprint px-1 text-center text-xs items-center rounded-lg p-2 bg-yellow-200 hover:bg-yellow-300"
                                onclick="handlePrintClick()">
                                列印
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="DataRows flex-grow overflow-y-auto">
                    @forelse ($machineAuthKeys as $key)
                        <div class="flex items-center border-b border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                            <div id="chipKey" class="w-[4%] text-xs break-words items-center text-center">
                                {{ $key->id }}
                            </div>
                            <div id="authKey_{{ $key->id }}" {{-- Use unique ID --}}
                                class="w-[28%] text-xs break-words items-center text-center cursor-copy text-blue"
                                onclick="copyToClipboard('{{ $key->auth_key }}', 'authKey_{{ $key->id }}')"
                                {{-- Use new function --}} title="{{ $key->auth_key }}">{{ $key->auth_key }}
                            </div>
                            <div class="w-[20%] px-2 py-1  break-words  text-gray-500 text-center font-mono text-xs"
                                title="{{ $key->chip_hardware_id }}">
                                {{ $key->chip_hardware_id ?? '未設定' }}
                            </div>
                            <div class="w-[15%] px-1 py-1 c-list break-words text-center text-gray-500">
                                {{ $key->expires_at ? $key->expires_at->format('ymd H:i') : '永不' }}
                            </div>
                            <div class="w-[17%] px-1 py-1 text-center break-words ">
                                @if ($key->status === 'active')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        啟用
                                    </span>
                                @elseif($key->status === 'inactive')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        停用
                                    </span>
                                @else
                                    {{-- 'pending' 或其他狀態 --}}
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        待啟用
                                    </span>
                                @endif
                            </div>
                            <div class="w-[13%] px-1 py-1 whitespace-nowrap text-center text-gray-500">
                                {{ $key->creator->name ?? 'N/A' }}
                            </div>
                            <div class="w-[10%] px-2 py-1 whitespace-nowrap text-center font-medium">
                                {{-- 編輯按鈕 --}}
                                <a href="{{ route('admin.machine_auth_keys.edit', $key) }}" {{-- *** 編輯按鈕連結 *** --}}
                                    class="text-indigo-600 hover:text-indigo-900 mr-1" title="編輯">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </a>
                                {{-- 刪除按鈕 (使用表單提交) --}}
                                <form action="{{ route('admin.machine_auth_keys.destroy', $key) }}" method="POST"
                                    class="inline-block" {{-- 修改：如果已綁定機器，阻止提交 --}}
                                    onsubmit="if({{ $key->machine_id ? 'true' : 'false' }}) { alert('此金鑰已綁定機器，無法直接刪除。'); return false; } return confirm('您確定要刪除這個金鑰嗎？此操作無法復原！');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" {{-- 修改：如果已綁定機器，添加 disabled 屬性和樣式 --}}
                                        class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                        @if ($key->machine_id) disabled title="已綁定機器，無法刪除" @else title="刪除" @endif>
                                        <i class="bi bi-trash3"></i>
                                        {{-- 刪除 --}}
                                    </button>
                                </form> {{-- *** 修正：補上 form 結束標籤 *** --}}
                            </div>
                            <div class="w-[10%] break-words text-center">
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
            <form id="printForm" action="{{ route('admin.machine_auth_keys.print') }}" method="POST"
                style="display: none;">
                @csrf
                {{-- JavaScript 會在這裡動態添加 selected_ids[] input --}}
            </form>
            {{-- 分頁連結 --}}
            @if ($machineAuthKeys->hasPages())
                <div class="mt-4 px-4 py-2 bg-gray-50 border-t border-gray-200">
                    {{ $machineAuthKeys->links() }}
                </div>
            @endif

        </div>
    </div>


    {{-- 新增：JavaScript 確認函數 和 列印處理函數 --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                function confirmQuickAdd() { // 修改：簡化函數，直接讀取 select 的值
                    const quantitySelect = document.getElementById('auth-key-add-quantity');
                    if (!quantitySelect) {
                        alert('找不到數量選擇元素！');
                        return;
                    }
                    const quantity = quantitySelect.value; // 直接獲取選中的值

                    const confirmationMessage = `確定要新增 ${quantity} 個金鑰嗎？\n(擁有者將設為您自己，狀態為待啟用)`;

                    if (confirm(confirmationMessage)) { // 再次確認
                        console.log('Native confirm confirmed. Quantity:', quantity); // 調試日誌
                        console.log('Attempting to find elements by ID...'); // 調試日誌

                        // *** 修改：嘗試使用 querySelector ***
                        // const quantityInput = document.getElementById('auth-key-add-quantity'); // 不再需要單獨設置 input value
                        const form = document.getElementById('auth-key-add-form'); // 直接獲取表單

                        // *** 新增：更詳細的調試日誌 ***
                        console.log('Element search results:', {
                            // quantityInput, // 移除引用
                            form
                        });
                        console.log('Does form exist in DOM now?', document.body.contains(form));

                        if (!form) { // 修改：只檢查 form 是否存在
                            console.error('無法找到表單元素!', {
                                // quantityInputFound: false, // 移除引用
                                formFound: !!form // 保持對 form 的檢查
                            }); // 調試日誌
                            alert('頁面元素錯誤，無法提交請求。'); // 改用原生 alert
                            return; // 如果找不到元素，則停止執行
                        }

                        // quantityInput.value = quantity; // 不需要了，select 的值會隨表單提交

                        // 顯示載入遮罩 (如果 showLoadingOverlay 存在)
                        if (typeof showLoadingOverlay === 'function') {
                            showLoadingOverlay();
                        }
                        // 提交隱藏的表單
                        form.submit(); // 在找到的表單元素上調用 submit
                    } else {
                        console.log('Native confirm cancelled.'); // 調試日誌
                    }
                }

                // 將函數附加到 window 對象，以便 onclick 可以調用
                window.confirmQuickAdd = confirmQuickAdd;

            }); // DOMContentLoaded 結束

            // handlePrintClick 函數已在 layouts/app.blade.php 中定義，這裡不需要重複定義
            // function handlePrintClick() { ... }

            function handlePrintClick() {
                const selectedCheckboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');
                let selectedIds = [];
                selectedCheckboxes.forEach(checkbox => {
                    selectedIds.push(checkbox.value);
                });

                if (selectedIds.length === 0) {
                    alert('請至少選取一筆金鑰');
                    return;
                }

                const printForm = document.getElementById('printForm');
                if (!printForm) {
                    alert('找不到列印表單元素！');
                    return;
                }
                printForm.querySelectorAll('input[name="selected_ids[]"]').forEach(input => input.remove());

                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_ids[]';
                    input.value = id;
                    printForm.appendChild(input);
                });
                printForm.submit();
            }

            // 將 handlePrintClick 函數附加到 window 對象，以便 onclick 可以調用
            window.handlePrintClick = handlePrintClick;
        </script>

        {{-- 新增：JavaScript 確認函數 和 列印處理函數 --}}
        {{-- <script>
function confirmQuickAdd() {
if (confirm('確定要快速新增一個金鑰嗎？\n(擁有者將設為您自己，狀態為 pending)')) {
    document.getElementById('quick-add-key-form').submit();
}
}
</script> --}}
    @endpush
@endsection
