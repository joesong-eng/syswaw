{{-- <nav x-data="{ open: false }" class="w-full inset-x-0 bg-white border-b border-gray-100 h-12 --}}
{{-- fixed top-0 left-0 z-50  --}}
{{-- "> --}}
<!-- Primary Navigation Menu -->
{{-- <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 mx-auto px-4 sm:px-6 lg:px-8 sm:shadow-lg h-12 "> --}}
<div class="flex justify-between h-12 w-full">
    <div class="my-auto flex">
        <!-- L_item -->
        <div class="flex items-center">
            <!-- Logo -->
            {{-- <div class="shrink-0 flex items-center justify-center h-full">
                        <button class="focus:outline-none"> <!--  @click="toggleSidebar()" > -->
                            <div class="bg-logo1 dark:bg-logo2 h-16 w-16 bg-cover"></div>
                        </button>
                        <h2 class="font-bold text-gray-800 ms-2 dark:text-gray-200">{{ $title }}</h1>
                    </div> --}}
            <!-- Navigation Links -->
            {{-- <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                @php
                    $route = 'dashboard'; // 預設路由為 dashboard
                    if (Auth::check()) {
                        $userId = Auth::user()->id;
                        $rolename = Auth::user()->getRoleNames()->first();
                        $arr = [
                            'admin' => 'admin',
                            'arcade-owner' => 'arcade',
                            'arcade-staff' => 'arcade',
                            'machine-owner' => 'machine',
                            'machine-manager' => 'machine',
                            'member' => '',
                            'user' => '',
                        ];
                        $route = empty($rolename) ? 'dashboard' : $arr[$rolename] . '.dashboard';
                    } else {
                        return redirect()->route('login');
                    }
                @endphp

                <x-nav-link href="{{ route($route) }}" :active="request()->routeIs($route)">
                    {{ __('msg.' . $route) }}
                </x-nav-link>
            </div> --}}
            {{-- @if (optional(Auth::user()->store)->name)
                <span class="ps-5 ms-2 font-thin text-sm dark:text-gray-200">{{ Auth::user()->store->name }}</span>
            @endif --}}
        </div>
    </div>
    <div class="my-auto flex"><!-- R_item -->
        <!-- Language Dropdown -->
        <div class="sm:-my-px sm:ms-10 sm:flex space-x-4 h-full ">
            <x-dropdown align="right" width="48">
                <!-- 觸發器 -->
                <x-slot name="trigger" class="flex">
                    <button
                        class="font-medium 
                                flex justify-between space-x-2 p-2 
                                text-gray-900 
                                hover:text-gray-700 
                                dark:text-gray-100 
                                dark:hover:text-white 
                                focus:outline-none">
                        <x-nav-link class="text-sm px-3">
                            @switch(app()->getLocale())
                                @case('zh-TW')
                                    中文
                                @break

                                @case('zh-CN')
                                    简体
                                @break

                                @case('en')
                                    English
                                @break

                                @default
                                    Language
                            @endswitch
                        </x-nav-link>
                    </button>
                </x-slot>
                <!-- 內容 -->
                <x-slot name="content" class="">
                    <div class="flex justify-between space-x-2 mx-auto px-2">
                        <a href="/lang/zh-TW">中文 </a>
                        <a href="/lang/zh-CN">简体 </a>
                        <a href="/lang/en">English</a>
                    </div>
                </x-slot>
            </x-dropdown>
        </div>

        <!-- user info -->
        <div class="mx-2 flex items-center max-w-full">
            <button @click="open = ! open"
                class="items-center justify-center px-2 pt-3 
                        text-gray-300 
                        transition duration-150 ease-in-out">
                {{-- <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg> --}}
                <span
                    class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:text-gray-700 dark:hover:text-300 ">
                    {{ Auth::user()->name }}
                    {{-- <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg> --}}
                </span>
            </button>
        </div>
    </div>
</div>
{{-- </div> --}}
<div id="current-time"
    class="c-list fixed right-0 z-50 flex text-sm text-green-600 dark:text-green-300 justify-end text-right w-[40%] mx-2 p-0 top-0">
    @php
        date_default_timezone_set('Asia/Taipei');
        echo date('ymd H:i');
    @endphp
</div>

<!-- Responsive Navigation Menu -->
<div x-cloak :class="{ 'block': open, 'hidden': !open }"
    class="absolute z-50
        bg-white dark:bg-gray-800 
        text-gray-700 dark:text-gray-300 
        border-gray-100 dark:border-gray-700
        max-w-48
        rounded-md shadow-lg top-10 right-0">
    <div
        class="pt-2 pb-3
            bg-white dark:bg-gray-800 
            text-gray-700 dark:text-gray-300 
            space-y-1">
        @if (Auth::user()->hasRole('admin'))
            <x-responsive-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                {{ __('msg.admin.dashboard') }}
            </x-responsive-nav-link>
        @else
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('msg.dashboard') }}
            </x-responsive-nav-link>
        @endif
    </div>

    <!-- Responsive Settings Options -->
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="flex items-center px-4">
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div class="shrink-0 me-3">
                    <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}"
                        alt="{{ Auth::user()->name }}" />
                </div>
            @endif

            <div>
                <div
                    class="font-medium 
                    text-base text-gray-700 
                    dark:text-gray-300">
                    {{ Auth::user()->name }}</div>
                <div class="font-thin text-sm text-gray-700 dark:text-gray-300">{{ Auth::user()->email }}</div>
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <!-- Account Management -->
            <x-responsive-nav-link href="{{ route('profile') }}" :active="request()->routeIs('profile.show')">
                {{ __('msg.profile') }}
            </x-responsive-nav-link>

            {{-- @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif --}}
            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf
                <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                    {{ __('msg.logout') }}
                </x-responsive-nav-link>
            </form>

        </div>
    </div>
</div>



{{-- </nav> --}}
