    <x-guest-layout>
        @if (session('alert'))
            <script>
                alert("{{ session('alert') }}");
                window.location.href = '/';  // 重新跳轉到首頁
            </script>
        @endif
        @if (session('success'))
        <div class="message mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
        <div class="relative sm:flex sm:justify-center sm:items-center bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('admin_manager'))
                        <a href="{{ route('admin.dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ 'msg.dashboard' }}</a>
                        @else
                        <a href="{{ route('dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ 'msg.dashboard' }}</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('msg.login') }}</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('msg.register') }}</a>
                        @endif
                    @endauth
                </div>
            @endif
            <div class="max-w-2xl mx-auto py-3 px-6">
                <div class="flex justify-center">
                    <x-authentication-card-logo />
                </div>
    
                <div class="mt-16">
                    {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8"> --}}
                        <a class="scale-100 p-3 bg-gray dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                            <div>
                                {{-- <div class="h-16 w-16 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7 stroke-red-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5C7.30558 4.5 3.5 8.30558 3.5 13S7.30558 21.5 12 21.5 20.5 17.6944 20.5 13 16.6944 4.5 12 4.5zM12 6.042A6.968 6.968 0 0118 13a6.968 6.968 0 01-6 6.958V6.042z" />
                                        </svg>
                                </div> --}}
    
                                <h2 class="mt-6 text-xl font-semibold text-gray-500 dark:text-gray-400 ">
                                    {{ __('msg.homepage.tagline') }}</h2>
                                <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                    {{ __('msg.homepage.description.line1') }}
                                    {{ __('msg.homepage.description.line2') }}
                                    {{ __('msg.homepage.description.line3') }}
                                </p>
                            </div>
                        </a>
                    {{-- </div> --}}
                </div>
            </div>
        </div>

    </x-guest-layout>
