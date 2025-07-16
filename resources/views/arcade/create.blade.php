@extends('layouts.app')
@section('content')
    @php
        $route = Auth::user()->hasRole('admin') ? 'admin.arcades.store' : 'arcade.store';
    @endphp
    <div class="mx-auto max-w-md">
        <div class="shadow-lg rounded-lg p-4">
            <div class="py-2 px-3">
                <h4 class="font-semibold text-md text-gray-800 leading-tight">
                    {{ __('msg.create_arcade') }}</h4>
                <small class="text-gray-600">請輸入商鋪的詳細資訊,同時將自動註冊老闆管理帳號</small>
            </div>
            <hr>
            <form action="{{ route($route) }}" method="POST">
                @csrf
                @if (!Auth::user()->hasRole('admin'))
                    <div class="mt-4">
                        <x-label for="arcadeKey" class="text-gray-700">{{ __('msg.token') }}:</x-label>
                        <x-input id="arcadeKey" class="input-st w-full" type="text" name="arcadeKey"
                            value="{{ $arcadeKey ?? '' }}" required autofocus autocomplete="arcadeKey"
                            onpaste="handlePaste(event)" />
                    </div>
                @else
                    <input id="arcadeKey" class="input-st w-full" type="text" name="arcadeKey"
                        value="{{ $arcadeKey ?? '' }}" readonly />
                @endif

                <div class="mt-4"><!-- 商鋪名稱欄位 -->
                    <x-label for="arcade_name" class="text-gray-700">{{ __('msg.name') }}:</x-label>
                    <x-input id="arcade_name" class="w-full" type="text" name="arcade_name" required />
                </div>
                <div class="mt-4"><!-- 商鋪地址欄位 -->
                    <x-label for="address" class="block text-gray-700">{{ __('msg.address') }}:</x-label>
                    <x-input type="text" id="address" name="address" class="w-full" />
                </div>
                <div class="mt-4"><!-- 商鋪電話欄位 -->
                    <x-label for="phone" class="block text-gray-700">{{ __('msg.phone') }}:</x-label>
                    <x-input type="text" id="phone" name="phone" class="w-full" />
                </div>
                <div class="mt-4"><!-- 遊藝場幣種欄位 -->
                    <x-label for="currency" class="text-gray-700">{{ __('msg.currency') }}:</x-label>
                    <!-- 需要新增翻譯 'currency' -->
                    <select id="currency" name="currency"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required>
                        @foreach (config('bill_mappings', []) as $code => $displayNameKey)
                            <option value="{{ $code }}">
                                {{ __('msg.' . $code) }}
                                ({{ __($code) }})
                            </option>
                        @endforeach
                        {{-- 可以根據需要添加更多幣種 --}}
                    </select>
                </div>
                <div class="mt-4">
                    <!-- 商鋪類型欄位 'physical_arcade' 'virtual_arcade' -->
                    @if (Auth::user()->hasRole('arcade-owner'))
                        <input type="text" id="type_physical" name="type" value="physical">
                    @endif
                    @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('machine-owner'))
                        <label class="block text-gray-700">{{ __('msg.arcade') }}{{ __('msg.type') }}：</label>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="radio" id="type_physical" name="type" value="physical" class="input-st"
                                    required>
                                <span class="ml-2">{{ __('msg.physical_arcade') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="radio" id="type_virtual" name="type" value="virtual" class="input-st"
                                    required>
                                <span class="ml-2">{{ __('msg.virtual_arcade') }}</span>
                            </label>
                        </div>
                    @endif
                </div>
                <hr class="mt-4 p-3">
                @guest
                    <div class="font-medium text-lg">
                        {{ __('註冊新店主帳戶') }}
                    </div>
                    <div class="mt-4"><!-- 店主帳戶欄位 -->
                        <x-label for="user-name" value="{{ __('Name  - 店主帳戶 -') }}" class="text-gray-700" />
                        <x-input id="user-name" class="w-full" type="text" name="user_name" required autofocus
                            autocomplete="name" />
                    </div>
                    <div class="mt-4"><!-- 店主郵件欄位 -->
                        <x-label for="email" value="{{ __('Email  - 店主郵箱 -') }}" class="text-gray-700" />
                        <x-input id="email" class="w-full" type="email" name="new_user_email" required
                            autocomplete="username" />
                    </div>
                    <div class="mt-4"><!-- 店主密碼欄位 -->
                        <x-label for="password" value="{{ __('Password') }}" class="text-gray-700" />
                        <x-input id="password" class="w-full" type="password" name="password" required
                            autocomplete="new-password" />
                    </div>
                    <div class="mt-4"><!-- 店主密碼確認欄位 -->
                        <x-label for="password_confirmation" value="{{ __('msg.confirm_password') }}"
                            class="text-gray-700" />
                        <x-input id="password_confirmation" class="w-full" type="password" name="password_confirmation"
                            required autocomplete="new-password" />
                    </div>
                    <div class="py-4">
                        <label for="description" class="block text-gray-700">描述：</label>
                        <textarea id="description" name="description" rows="4" class="w-full"></textarea>
                    </div>
                @endguest
                <div class="flex justify-end my-4">
                    <x-button type="submit" class="transition">
                        {{ __('msg.create_arcade') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function handlePaste(event) {
            // 獲取貼上的文字
            let pasteData = (event.clipboardData || window.clipboardData).getData('text');
            // 定義要移除的前綴
            const prefixToRemove = 'https://sxs.tg25.win/arcades/';
            // 如果貼上的文字以指定前綴開頭，則移除前綴
            if (pasteData.startsWith(prefixToRemove)) {
                let cleanedData = pasteData.replace(prefixToRemove, '');
                event.target.value = cleanedData; // 設置清理後的數據
                event.preventDefault(); // 阻止默認貼上行為
            }
        }
    </script>
@endsection
@php
    $title = __('msg.create_arcade');
@endphp
