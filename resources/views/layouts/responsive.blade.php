<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @csrf
    <title>{{ $title ?? config('app.name', 'SYS') }}</title> {{-- Use $title if passed, otherwise default --}}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&family=Montserrat:wght@300;400;700&family=Figtree:wght@300;400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
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

        #header {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 16px;
            border: 1px solid #badbcc;
            border-radius: 8px;
            overflow: hidden;
            transition: opacity 0.5s ease, height 0.5s ease;
        }

        .hidden {
            display: none;
        }

        .c-list,
        .c-list * {
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 300;
            /* 使用瘦細字體 */
        }

        .sidr {
            border-right: 1px solid rgb(125, 125, 252);
            overflow: auto;
            display: flex;
            align-items: center;
        }

        .of {
            overflow-wrap: break-word;
        }

        @media (min-width: 640px) {
            .sm\:block {
                display: block;
            }
        }

        #sidebarkid {
            width: 100%;
            overflow-x: auto;
            /* 這樣可以觸發水平方向滾動條 */
            white-space: nowrap;
            /* 確保子元素不會換行 */
        }

        #sidebarkid {
            width: 100%;
            overflow-x: auto;
            /* 支持水平滾動 */
            white-space: nowrap;
            /* 內容不換行 */
        }

        /* 隱藏滾動條 */
        #sidebarkid::-webkit-scrollbar {
            display: none;
        }

        /* 在你的 CSS 檔案中添加以下樣式 */
        @keyframes slideIn {
            from {
                transform: translateY(-300%);
            }

            to {
                transform: translateY(0);
            }
        }

        @keyframes slideOut {
            from {
                transform: translateY(0);
            }

            to {
                transform: translateY(-300%);
            }
        }

        .slide-in {
            animation: slideIn 1s forwards;
        }

        .slide-out {
            animation: slideOut 1s forwards;
        }

        .bg-logo1 {
            background-image: url('{{ asset('storage/images/iotlink_01.png') }}');
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

        [x-cloak] {
            display: none !important;
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
    </style>
</head>

<body class="font-sans antialiased bg-gray-100" x-data="{ isSidebarOpen: false, transitionsEnabled: false }" x-init="setTimeout(() => { transitionsEnabled = true }, 50)">
    <!-- Layout Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" {{-- Sidebar --}}
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform -translate-x-full md:relative md:translate-x-0 flex flex-col"
            x-cloak {{-- 1. 添加 x-cloak --}}
            :class="{
                'transition-transform duration-300 ease-in-out': transitionsEnabled, // 2. 恢復 transition 條件
                'translate-x-0': isSidebarOpen {{-- 2. 只在展開時添加 translate-x-0 --}}
            }">
            <div id="bg-sidebar"
                class="flex items-center justify-center h-12 bg-gray-100 flex-shrink-0">
                {{-- Placeholder for Logo or Title --}}
                <div class="shrink-0 flex items-center justify-center h-full">
                    <button class="focus:outline-none">
                        <div class="bg-logo1 h-16 w-16 bg-cover"></div>
                    </button>
                    {{-- <h2 class="font-bold text-gray-800 ms-2">{{ $title }}</h1> --}}
                </div>
                {{-- <span class="text-gray-800 text-lg font-bold">SYS</span> --}}
            </div>
            <nav class="mt-4 px-2 space-y-1 flex-1 overflow-y-auto pb-4"> {{-- 3. 添加 flex-1 overflow-y-auto 和 padding-bottom --}}
                @for ($i = 0; $i < 30; $i++)
                    <a href="#"
                        class="flex items-center px-2 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100">
                        <i class="bi bi-circle mr-3"></i> Link {{ $i + 1 }}
                    </a>
                @endfor
                {{-- Add more links as needed --}}
            </nav>
        </aside>
        <!-- Mobile Overlay -->
        <div x-show="isSidebarOpen" {{-- Mobile Overlay --}} class="fixed inset-0 z-30 bg-black bg-opacity-50 md:hidden"
            x-cloak @click="isSidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100">
        </div>
        <!-- End Sidebar Overlay -->

        <!-- Main Content Area -->
        <div class="relative flex-1 flex flex-col overflow-hidden w-full"> {{-- Main Area Wrapper --}}
            <!-- Top Navigation Bar -->
            <header {{-- Navbar / Top Navbar --}}
                class="bg-white shadow-md h-12 flex items-center justify-between px-4 md:px-6 w-full flex-shrink-0">
                <!-- Mobile Menu Button -->
                <button @click="isSidebarOpen = !isSidebarOpen"
                    class="text-gray-500 focus:outline-none md:hidden">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Header Content / Title -->
                <div class="flex-1 flex justify-center md:justify-start">
                    @hasSection('header')
                        <div class="max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    @endif
                </div>

                <!-- User Menu / Other Icons -->
                <div class="flex items-center">
                    {{-- Placeholder for user dropdown, notifications etc. --}}
                    <span class="text-gray-700">User Menu</span>
                </div>
            </header>
            <!-- End Top Navigation Bar -->

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100"> {{-- Page Content Area --}}
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 xl:max-w-7xl">
                    @yield('content')
                </div>
            </main>
            <!-- End Page Content -->

        </div>
        <!-- End Main Content Area -->

    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
