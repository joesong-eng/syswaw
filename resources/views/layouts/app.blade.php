<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'SYS') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    {{-- 將通用 CSS 樣式放入 head 區塊，使用 @stack('styles') 引入 --}}
    {{-- @stack('styles') --}}
    {{-- <style>
        [x-cloak] {
            display: none !important;
        }

        /* 遮罩樣式 */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            /* 白色半透明背景 */
            z-index: 9999;
            /* 確保在最上層 */
            display: none;
            /* 預設隱藏 */
            justify-content: center;
            align-items: center;
        }

        /* 轉圈動畫 */
        .loading-overlay .spinner {
            border: 6px solid #f3f3f3;
            /* 淺灰色邊框 */
            border-top: 6px solid #3498db;
            /* 藍色邊框 */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        /* 動畫定義 */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .c-list,
        .c-list * {
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 300;
            /* 使用瘦細字體 */
        }

        @media (min-width: 640px) {
            .sm\:block {
                display: block;
            }
        }

        #sidebarkid::-webkit-scrollbar {
            display: none;
        }

        .bg-logo1 {
            background-image: url("{{ asset('storage/images/iotlink_01.png') }}");
        }

        .bg-logo2 {
            background-image: url('{{ asset('storage/images/iotlink_02.png') }}');
        }

        /* 暗黑模式下切換背景圖片 */
        @media (prefers-color-scheme: dark) {
            .bg-logo1 {
                background-image: url('{{ asset('storage/images/iotlink_02.png') }}');
            }

            .bg-logo2 {
                background-image: url('{{ asset('storage/images/iotlink_01.png') }}');
            }
        }

        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding: 1px 20px 1px 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-position: right 5px center;
            background-size: 16px;
        }

        @layer components {
            .card-bg {
                @apply bg-white dark:bg-gray-800;
            }

            .text-default {
                @apply text-gray-700 dark:text-gray-100;
            }

            .border-default {
                @apply border-gray-200 dark:border-gray-700;
            }
        }

        :root {
            --table-max-height: calc(100vh - 350px);
            --table-max-height-sm: calc(100vh - 155px);
        }
    </style> --}}
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900" x-data="{ isSidebarOpen: false, transitionsEnabled: false }" x-init="setTimeout(() => { transitionsEnabled = true }, 50)">
    <div class="flex h-screen overflow-hidden">
        <aside id="sidebar" {{-- Sidebar --}}
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 shadow-lg transform -translate-x-full md:relative md:translate-x-0 flex flex-col"
            x-cloak {{-- 1. 添加 x-cloak --}}
            :class="{
                'transition-transform duration-300 ease-in-out': transitionsEnabled, // 2. 恢復 transition 條件
                'translate-x-0': isSidebarOpen {{-- 2. 只在展開時添加 translate-x-0 --}}
            }">
            <div
                class="flex items-center justify-center h-12 bg-white  dark:bg-gray-900 flex-shrink-0 border border-gray-200 dark:border-gray-700">
                {{-- Placeholder for Logo or Title --}}
                <div class="shrink-0 flex items-center justify-center h-full ">
                    <button class="focus:outline-none">
                        <div class="bg-logo1 dark:bg-logo2 h-16 w-16 bg-cover"></div>
                    </button>
                </div>
            </div>
            @if (Auth::check() && !Auth::user()->hasVerifiedEmail())
                <div
                    class="p-3 m-2 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 dark:bg-yellow-700 dark:text-yellow-100 dark:border-yellow-300 rounded-md shadow-md">
                    <p class="text-sm font-medium">{{ __('msg.email_not_verified_title') }}</p>
                    <p class="text-xs mt-1">{{ __('msg.email_not_verified_message') }}</p>
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                        @csrf
                        <div>
                            <x-button type="submit" class="text-xs !py-1 !px-2">
                                {{ __('msg.resend_verification_email') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            @endif
            <nav class="mt-2 px-2 space-y-1 flex-1 overflow-y-auto pb-4">
                @if (Auth::check() && Auth::user()->roles()->count() > 0)
                    <x-modal.sidebar class="relative pt-14 flex-shrink-0" />
                @endif
            </nav>
        </aside>

        <div id="bg-sidebar" x-show="isSidebarOpen" {{-- Mobile Overlay --}}
            class="fixed inset-0 z-30 bg-black bg-opacity-50 md:hidden" x-cloak @click="isSidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100">
        </div>
        <div class="relative flex-1 flex flex-col overflow-hidden w-full"> {{-- Main Area Wrapper --}}
            <header
                class="bg-white dark:bg-gray-800 shadow-md h-12 flex items-center justify-between w-full flex-shrink-0 p-3">
                <button @click="isSidebarOpen = !isSidebarOpen"
                    class="text-gray-500 dark:text-gray-400 focus:outline-none md:hidden">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex-1 flex justify-start md:justify-start">
                    @hasSection('header')
                        <div class="max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    @endif
                </div>
                {{-- 移除 w-full，讓 nav 根據內容寬度或 flex 規則調整，並添加 flex-shrink-0 防止被過度壓縮 --}}
                <nav x-data="{ open: false }"
                    class="inset-x-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 h-12 flex-shrink-0">
                    @include('navigation-menu', ['title' => $title ?? ''])
                </nav>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900">
                {{-- Page Content Area --}}
                <div class="container max-h-full relative mx-auto px-1 lg:px-8 py-1 xl:max-w-7xl">
                    {{-- 這裡的 session 訊息可以使用 Livewire 的提示功能來優化，但為了保留你的結構暫不修改 --}}
                    @if (session('success'))
                        <div class="absolute bg-green-100 text-green-800 p-3 rounded z-10">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="absolute bg-red-100 text-red-800 p-3 rounded z-10">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="absolute bg-yellow-100 text-yellow-800 p-3 rounded z-10">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    {{-- 添加全局狀態指示器 --}}
    <div id="global-reverb-status"
        class="fixed bottom-2 right-2 text-xs px-2 py-1 rounded bg-gray-500 text-white z-50 opacity-75">
        連接中...
    </div>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    @livewireScripts

    {{-- 使用 @stack('scripts') 來引入所有自訂 JavaScript --}}
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- 將你的 style 放到 @push('styles') 區塊中 --}}
    @push('styles')
    @endpush

    {{-- 將你的 script 放到 @push('scripts') 區塊中 --}}
    @push('scripts')
        <script>
            function showLoadingOverlay(id = 'loadingOverlay') {
                document.getElementById(id).style.display = 'flex';
            }

            function hideLoadingOverlay(id = 'loadingOverlay') {
                document.getElementById(id).style.display = 'none';
            }
            // 綁定表單提交事件
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', async (event) => {
                        if (form.dataset.preventSubmit === 'true') {
                            return; // 停止提交與 loadingOverlay
                        } // 检查是否有特定class或data属性
                        const isConfirmSubmit = form.classList.contains('confirm-submit');
                        const confirmationMessage = form.getAttribute('data-confirm') ||
                            (isConfirmSubmit ? '{{ __('msg.add_token') }}' : null);

                        // 需要确认的情况
                        if (confirmationMessage) {
                            event.preventDefault();
                            try {
                                const result = await Swal.fire({
                                    title: '{{ __('msg.confirm') }}',
                                    text: confirmationMessage,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: '{{ __('msg.yes') }}',
                                    cancelButtonText: '{{ __('msg.cancel') }}'
                                });
                                if (result.isConfirmed) {
                                    console.log(
                                        'User confirmed the action'
                                    ); // Log when user clicks Confirm
                                    showLoadingOverlay();
                                    form.submit();
                                } else {
                                    console.log(
                                        'User canceled the action'); // Log when user clicks Cancel
                                    hideLoadingOverlay();
                                }
                            } catch (error) {
                                console.error('Confirmation dialog error:', error);
                                hideLoadingOverlay();
                            }
                        } else {
                            showLoadingOverlay();
                        }
                    });
                });
                // **** 修改：先檢查 sidebarkid 是否存在 ****
                const sidebarkid = document.getElementById('sidebarkid');
                if (sidebarkid) { // 只有當 sidebarkid 存在時才執行以下操作
                    const scrollPosition2 = localStorage.getItem('sidebarkid-scroll') || 0;
                    sidebarkid.scrollLeft = scrollPosition2; // 滾動到記住的位置
                    sidebarkid.addEventListener('scroll', function() {
                        localStorage.setItem('sidebarkid-scroll', sidebarkid.scrollLeft);
                    }, {
                        passive: true
                    });
                }

                const successMessage = document.getElementById('success-message');
                const errorMessage = document.getElementById('error-message');
                if (successMessage) {
                    console.log('successMessage in');
                    setTimeout(() => {
                        successMessage.classList.add('slide-out');
                    }, 3000); // 停留3秒
                }
                if (errorMessage) {
                    setTimeout(() => {
                        errorMessage.classList.add('slide-out');
                    }, 10000); // 停留10秒
                }
            }, {
                passive: true
            });

            function showLocalMessage() { // 如果消息容器不存在，则动态创建并插入到 <header> 底下
                let successMessage = document.getElementById('success-message');
                if (!successMessage) {
                    successMessage = document.createElement('div');
                    successMessage.id = 'success-message';
                    successMessage.className =
                        'absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg duration-1000 ease-out transform transition-transform slide-in';
                    document.querySelector('header').appendChild(successMessage); // 插入到 <header> 底下
                }

                // 设置消息内容和样式
                successMessage.textContent = '{{ __('msg.err_del_arcadeKey') }}'; // 使用 Laravel 翻译函数
                successMessage.className =
                    'absolute message ms-4 p-2 text-red-800 bg-red-100 z-10 border border-red-200 rounded-lg duration-1000 ease-out transform transition-transform slide-in';

                // 3 秒后自动隐藏消息
                setTimeout(() => {
                    successMessage.classList.add('slide-out');
                    successMessage.textContent = ''; // 清空内容
                }, 3000);
            }

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
            window.handlePrintClick = handlePrintClick;
            /**
             * 將時間戳或日期物件格式化為 'YY-MMDD HH:MM:SS' 格式。
             * @param {string|number|Date|null} timestamp - 輸入的時間戳 (可以是 ISO 字串、Unix 時間戳毫秒數或 Date 物件)。如果為 null 或 undefined，則使用當前時間。
             * @returns {string} 格式化後的日期字串，或在無效時返回 'N/A'。
             */
            function formatTimestamp(timestamp = null) {
                // 如果 timestamp 為 null 或 undefined，則使用當前時間
                const date = timestamp ? new Date(timestamp) : new Date();

                // 檢查日期是否有效
                if (isNaN(date.getTime())) {
                    return 'N/A';
                }

                // 格式化日期
                const year = date.getFullYear().toString().slice(-2); // 取年份後兩位
                const month = (date.getMonth() + 1).toString().padStart(2, '0'); // 月份補零
                const day = date.getDate().toString().padStart(2, '0'); // 日期補零
                const hours = date.getHours().toString().padStart(2, '0'); // 小時補零
                const minutes = date.getMinutes().toString().padStart(2, '0'); // 分鐘補零
                const seconds = date.getSeconds().toString().padStart(2, '0'); // 秒數補零

                // 組合成 YY-MMDD HH:MM:SS 格式
                return `${year}-${month}${day} ${hours}:${minutes}:${seconds}`;
            }
        </script>
        <script>
            function copyToClipboard(text, elementId) {
                navigator.clipboard.writeText(text).then(function() {
                    // Optional: Provide visual feedback
                    const element = document.getElementById(elementId);
                    if (element) {
                        const originalText = element.innerText;
                        element.innerText = '{{ __('msg.copied') }}'; // Display "Copied!"
                        element.classList.add('text-green-500'); // Change color
                        setTimeout(() => {
                            element.innerText = originalText;
                            element.classList.remove('text-green-500');
                        }, 1500); // Revert after 1.5 seconds
                    }
                    console.log('Async: Copying to clipboard was successful!');
                }, function(err) {
                    console.error('Async: Could not copy text: ', err);
                    // Optional: Show an error message to the user
                });
            }
        </script>
    @endpush
</body>

</html>
