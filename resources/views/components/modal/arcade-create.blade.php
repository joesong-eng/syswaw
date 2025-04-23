@extends('layouts.app')
@section('content')
    {{-- <x-modal.arcade-create :arcadeKey="$arcadeKey" /> --}}

    <div class="justify-center mx-auto max-w-2xl">
        <div class="shadow-lg rounded-lg p-4">
            <div class="py-2 px-3">
                <h4 class="font-semibold text-md text-gray-800 dark:text-gray-200 leading-tight">新增商鋪</h4>
                <small class="text-gray-600 dark:text-gray-300">請輸入商鋪的詳細資訊,同時將自動註冊老闆管理帳號</small>
            </div>
            <hr>
            @if (session('success'))
                <div id="success-message" class="message mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                    <ul> 
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach 
                    </ul>
                </div>
            @endif
            @php
            $route= (Auth::user()->hasRole('admin')) ? route('admin.arcade.store'):$route= route('arcade.store');
            @endphp
            <form action="{{ $route }}" method="POST">
                @csrf
                @guest
                    <input type="hidden" name="arcadeKey" value="{{ $arcadeKey }}">
                @endguest
                @auth
                <div class="mt-4"><!-- 商鋪金鑰欄位 -->
                    <x-label for="arcadeKey" value="{{ $arcadeKey ?? '' }}" class=" text-gray-700 dark:text-gray-300" />
                    <x-input id="arcadeKey" class="input-st" type="text" name="arcadeKey" required autofocus autocomplete="arcadeKey" />
                </div>
                {{-- <div class="mt-4"><!-- 商鋪金鑰欄位 -->
                    
                    <label for="arcadeKey" class="block text-gray-700 dark:text-gray-300">商鋪金鑰：</label>
                    <x-input type="text" id="arcadeKey" name="arcadeKey" class="input-st"
                           value="{{ $arcadeKey ?? '' }}">
                </div> --}}
            @endauth
                <div class="mt-4"><!-- 商鋪名稱欄位 -->
                    <label for="arcade_name" class="block text-gray-700 dark:text-gray-300">商鋪名稱：</label>
                    <input type="text" id="arcade_name" name="arcade_name" 
                            class="input-st" 
                            required>
                </div>
                {{-- 這裡加上type --}}
                <div class="mt-4"><!-- 商鋪類型欄位 'physical_arcade' 'virtual_arcade' -->
                    <label for="type" class="block text-gray-700 dark:text-gray-300">{{__('msg.arcade')}}{{__('msg.type')}}：</label>
                    <select id="type" name="type" class="input-st" required>
                        <option value="Physical Arcade">{{__('msg.physical_arcade')}}</option>
                        <option value="Virtual Arcade">{{__('msg.virtual_arcade')}}</option>
                    </select>
                </div>
                <div class="mt-4"><!-- 商鋪地址欄位 -->
                    <label for="address" class="block text-gray-700 dark:text-gray-300">商鋪地址：</label>
                    <input type="text" id="address" name="address" 
                            class="input-st">
                </div>
                <div class="mt-4"><!-- 商鋪電話欄位 -->
                    <label for="phone" class="block text-gray-700 dark:text-gray-300">商鋪電話：</label>
                    <input type="text" id="phone" name="phone" 
                            class="input-st">
                </div>
                <hr class="mt-4 p-3">
                @guest
                <div class="font-medium text-lg">
                    {{__('註冊新店主帳戶')}}
                </div>
                <div class="mt-4"><!-- 店主帳戶欄位 -->
                    <x-label for="user-name" value="{{ __('Name  - 店主帳戶 -') }}" class=" text-gray-700 dark:text-gray-300" />
                    <x-input id="user-name" class="input-st" type="text" name="user_name" required autofocus autocomplete="name" />
                </div>
                <div class="mt-4"><!-- 店主郵件欄位 -->
                    <x-label for="email" value="{{ __('Email  - 店主郵箱 -') }}" class=" text-gray-700 dark:text-gray-300"/>
                    <x-input id="email" class="input-st" type="email" name="new_user_email" required autocomplete="username" />
                </div>
                <div class="mt-4"><!-- 店主密碼欄位 -->
                    <x-label for="password" value="{{ __('Password') }}" class=" text-gray-700 dark:text-gray-300"/>
                    <x-input id="password" class="input-st" type="password" name="password" required autocomplete="new-password" />
                </div>
                <div class="mt-4"><!-- 店主密碼確認欄位 -->
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class=" text-gray-700 dark:text-gray-300"/>
                    <x-input id="password_confirmation" class="input-st" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
    
                <div class="py-4">
                    <label for="description" class="block text-gray-700 dark:text-gray-300">描述：</label>
                    <textarea id="description" name="description" rows="4" 
                                class="input-st"></textarea>
                </div>
                @endguest
    
                <div class="flex justify-end my-4">
                    <button type="submit" class="inline-block font-semibold rounded-md shadow px-4 py-2 text-gray-700 hover:text-gray-200 bg-blue-500 hover:bg-blue-700 transition">
                        新增商鋪
                    </button>
                </div>
            </form>            
        </div>
    </div>
    @endsection
    @php
        $title =  __('msg.create_arcade'); ;
    @endphp
    
