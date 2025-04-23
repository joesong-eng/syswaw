<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @csrf
    <title>{{ config('app.name', 'SYS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&family=Montserrat:wght@300;400;700&family=Figtree:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<style>
    /* 遮罩樣式 */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4); /* 白色半透明背景 */
        z-index: 9999; /* 確保在最上層 */
        display: none; /* 預設隱藏 */
        justify-content: center;
        align-items: center;
    }

    /* 轉圈動畫 */
    .loading-overlay .spinner {
        border: 6px solid #f3f3f3; /* 淺灰色邊框 */
        border-top: 6px solid #3498db; /* 藍色邊框 */
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
</style>

<body class="dark:bg-gray-900">
    <div class="flex ">
        @auth
            @include('navigation-menu', ['title' => $title ?? ''])
            @if(Auth::check() && Auth::user()->roles()->count() > 0)
                <x-modal.sidebar class="relative pt-14 flex-shrink-0" />
            @endif
            <div class="container mx-auto pt-24 sm:pt-12">
                <div class="content ">
                    <div class="bg-gray-100 dark:bg-gray-900  h-[calc(100vh-110px)] sm:h-[calc(100vh-48px)]">
                        @if (!auth()->user()->hasVerifiedEmail())
                        <div id="alert-warning" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 relative" role="alert">
                            <span class="block sm:inline">
                                <strong class="font-bold">{{ __('msg.contact.title') }}</strong>
                                {{ __('msg.emailVerified') }}
                                <a href="{{ route('verification.notice') }}" class="underline text-yellow-800 hover:text-yellow-600">{{ __('msg.clickToVerify') }}</a>
                            </span>
                            <button onclick="document.getElementById('alert-warning').remove();" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-yellow-700 hover:text-yellow-900">
                                <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M14.348 14.849a1 1 0 01-1.414 0L10 11.415l-2.934 2.934a1 1 0 01-1.414-1.414l2.934-2.934-2.934-2.934a1 1 0 011.414-1.414L10 8.586l2.934-2.934a1 1 0 011.414 1.414L11.415 10l2.934 2.934a1 1 0 010 1.415z" />
                                </svg>
                            </button>
                        </div>
                        @endif

                        <header class="relative bg-gray-200 dark:bg-gray-800 shadow">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                                {{ $header ?? '' }}
                            </div>
                        </header>
                        @if (session('success'))
                            <div id="success-message" class="absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg  duration-1000 ease-out transform transition-transform slide-in">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div id="error-message" class="absolute message ms-4 p-2 text-red-600 bg-red-100 z-10 border border-red-200 rounded-lg  duration-1000 ease-out transform transition-transform slide-in">
                                <ul><li>{{ session('error') }}</li></ul>
                            </div>
                        @endif
                        @if ($errors->any())
                        <div id="error-message" class="absolute z-20 mb-4 text-red-600 bg-red-200  rounded-lg  duration-1000 ease-out transform transition-transform slide-in">
                            <ul class="bg-red-200 p-3 rounded-md ">
                                @foreach ($errors->all() as $error)
                                <li >{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <main>
                            @if(!Auth::user()->arcade && Auth::user()->hasRole('arcade-owner'))
                            <div class="relative bg-gray-100 m-auto">
                                <p class="absolute text-xl w-full flex top-0 text-red-600 font-medium dark:text-gray-100 p-2 animate-blink">
                                <a class="m-auto"href="{{ route('arcade.create') }}"> {{ __('msg.bind_arcade') }}</a>
                            </p>
                            </div>
                            @endif
                            @yield('content')
                        </main>
                    </div>
                </div>
            </div>
        @else
        @guest
            <script>
                window.location.href = "{{ route('login') }}";
            </script>
        @endguest
        @endauth
    </div>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
<script>
    function showLoadingOverlay() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoadingOverlay() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
    // 綁定表單提交事件
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', async (event) => {
                // 检查是否有特定class或data属性
                const isConfirmSubmit = form.classList.contains('confirm-submit');
                const confirmationMessage = form.getAttribute('data-confirm') || 
                                        (isConfirmSubmit ? '{{ __("msg.add_token") }}' : null);

                // 需要确认的情况
                if (confirmationMessage) {
                    event.preventDefault();
                    try {
                        const result = await Swal.fire({
                            title: '{{ __("msg.confirm") }}',
                            text: confirmationMessage,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: '{{ __("msg.yes") }}',
                            cancelButtonText: '{{ __("msg.cancel") }}'
                        });

                        if (result.isConfirmed) {
                            showLoadingOverlay();
                            form.submit();
                        }
                    } catch (error) {
                        console.error('确认对话框错误:', error);
                    }
                } 
                // 不需要确认的情况
                else {
                    showLoadingOverlay();
                    // 允许表单继续提交
                }
            });
        });
        const sidebarkid = document.getElementById('sidebarkid');// 綁定滑動位置
        const scrollPosition2 = localStorage.getItem('sidebarkid-scroll') || 0;
        sidebarkid.scrollLeft = scrollPosition2;// 滾動到記住的位置
        sidebarkid.addEventListener('scroll', function() {
            localStorage.setItem('sidebarkid-scroll', sidebarkid.scrollLeft);
        }, { passive: true});

        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');
        // const messageContainer = document.getElementById('local-message');
        if (successMessage) {
            console.log('successMessage in');
            setTimeout(() => {
                successMessage.classList.add('slide-out');
            }, 3000); // 停留10秒
        }
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.classList.add('slide-out');
            }, 10000); // 停留10秒
        }
    }, {passive: true} );
    
    function showLocalMessage() {// 如果消息容器不存在，则动态创建并插入到 <header> 底下
        let successMessage = document.getElementById('success-message');
        if (!successMessage) {
            successMessage = document.createElement('div');
            successMessage.id = 'success-message';
            successMessage.className = 'absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg duration-1000 ease-out transform transition-transform slide-in';
            document.querySelector('header').appendChild(successMessage); // 插入到 <header> 底下
        }

        // 设置消息内容和样式
        successMessage.textContent = '{{ __("msg.err_del_arcadeKey") }}'; // 使用 Laravel 翻译函数
        successMessage.className = 'absolute message ms-4 p-2 text-red-800 bg-red-100 z-10 border border-red-200 rounded-lg duration-1000 ease-out transform transition-transform slide-in';

        // 3 秒后自动隐藏消息
        setTimeout(() => {
            successMessage.classList.add('slide-out');
            successMessage.textContent = ''; // 清空内容
        }, 3000);
    }

    function copyToClipboardAndGenerateLink(Key, abstype) { //晶片或店面
        var textArea = document.createElement('textarea'); // 創建一個暫時的 textarea 元素來複製文本
        var stype = (abstype === 'chip') ? "{{ __('msg.chip') }}" : "{{ __('msg.arcade') }}";
        if (Key.charAt(0) === 'm') {
            // 如果是機器金鑰，直接複製到剪貼簿
            textArea.value = Key;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('機器金鑰已複製到剪貼簿');
        } else {
            // 如果是商店金鑰，生成 URL 並複製
            var baseUrl = window.location.origin;
            var storeUrl = `${baseUrl}/${abstype}/${Key}`;
            textArea.value = storeUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('API 金鑰已複製到剪貼簿,\n生成的 URL: \n' + storeUrl + '\n請交由客戶自行訪問即可新增' + stype);
        }
    }

    function handlePrintClick() {
        // 取得所有被選取的 checkbox（name 為 selected_ids[]）
        const selectedCheckboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');
        let selectedIds = [];
        selectedCheckboxes.forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });

        if (selectedIds.length === 0) {
            alert('請至少選取一筆金鑰');
            return;
        }

        // 動態建立表單提交給列印路由 (例如 route('printKeys') )
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('printKeys') }}';

        // 加入 CSRF token 隱藏欄位
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // 將被選取的金鑰 ID 插入隱藏欄位
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }
</script>
</html>