@extends('layouts.app')
@section('content')

    <div class="flex justify-center bg-gray-100 " 
        x-data="{ addMachineModal: false, showsuccess: true, showerrors: true , showHeader: true, storeType: false }">
        <div class="w-full max-w-2xl bg-white bg-opacity-60 shadow-lg rounded-lg">
            <div x-show="showHeader" class="relative bg-gray-200 shadow">
                @if (session('success'))
                    <button @click="showHeader = false" class="absolute top-0 right-0 p-2 text-gray-600">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div id="success-message" class="message mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <button @click="showHeader = false" class="absolute top-0 right-0 p-2 text-gray-600">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <!-- 頂部：標題和新增按鈕 -->
            {{-- <div class="flex items-center text-lg font-medium text-gray-700">
                <x-svg-icons name="list" classes="h-6 w-6" /> {{ __('msg.machine') }}{{ __('msg.list') }}
            </div> --}}
            <!-- 內容 -->
            <div class="flex justify-between px-3 pt-3">
                
                <div class="flex flex-col place-items-end w-full space-y-3 md:space-y-0 md:space-x-1">
                    <div class="flex justify-end w-full sm:w-auto">    <!-- 篩選框 -->
                        <form method="GET" class="w-full px-2" action="{{ route('machine.machines') }}">
                            <div class="relative inline-block">
                                <button type="button" 
                                    class="dropdown-btn w-32 py-2 px-4 border rounded-md text-gray-700
                                        {{ request('storeable_type') === 'vsstore' ? 'text-white bg-purple-500' : '' }}
                                        {{ request('storeable_type') === 'store' ? 'text-white bg-green-500' : '' }}" 
                                    @click="storeType = !storeType">
                                    {{ request('storeable_type') === 'vsstore' ? __('msg.vsstore') : 
                                    (request('storeable_type') === 'store' ? __('msg.store') : __('msg.all_stores')) }}
                                </button>

                                <div x-cloak x-show="storeType" class="dropdown-content w-full bg-white shadow-lg rounded-lg mt-2 absolute z-10 border border-gray-300">
                                    <label class="block px-4 py-2 text-sm font-medium cursor-pointer
                                    bg-white
                                    text-gray-700
                                    hover:bg-blue-100
                                    ">
                                        <input type="radio" name="storeable_type" value="" class="hidden"
                                            {{ request('storeable_type') === null ? 'checked' : '' }}
                                            onclick="this.form.submit();">
                                        {{ __('msg.all_stores') }}
                                    </label>

                                    <label class="block px-4 py-2 text-sm font-medium cursor-pointer
                                    bg-white
                                    text-gray-700
                                    hover:bg-blue-100
                                    ">               <input type="radio" name="storeable_type" value="vsstore" class="hidden"
                                            {{ request('storeable_type') === 'vsstore' ? 'checked' : '' }}
                                            onclick="this.form.submit();">
                                        {{ __('msg.vsstore') }}
                                    </label>

                                    <label class="block px-4 py-2 text-sm font-medium cursor-pointer
                                    bg-white
                                    text-gray-700
                                    hover:bg-blue-100
                                    ">                <input type="radio" name="storeable_type" value="store" class="hidden"
                                            {{ request('storeable_type') === 'store' ? 'checked' : '' }}
                                            onclick="this.form.submit();">
                                        {{ __('msg.store') }}
                                    </label>
                                </div>
                            </div>
                        </form>
                        <button @click="addMachineModal = true" class="px-4 py-2 w-2/3 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            {{ __('msg.create') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- 表格 -->
            <div class="shadow-md rounded-lg overflow-scroll p-2 " style="max-height: calc(100vh - 80px);">
                <!-- 表頭 -->
                <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                    <div class="w-[10%] px-1 text-center">{{ __('msg.name') }}</div>
                    <div class="w-[15%] px-1 text-center">{{ __('msg.type') }}</div>
                    <div class="w-[20%] px-1 text-center">{{ __('msg.store') }}</div>
                    <div class="w-[10%] px-1 text-center">{{ __('msg.revenue_split') }}</div>
                    <div class="w-[25%] px-1 text-center">{{ __('msg.api_key') }}</div>
                    <div class="w-[20%] px-1 text-center">{{ __('msg.status') }}|{{ __('msg.actions') }}</div>
                </div>

                <!-- 表內容 -->
<!-- 機器列表 -->
@foreach ($machines as $machine)
    <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
        <div class="w-[10%] break-words px-1 text-center">{{ $loop->iteration }}.{{ $machine->name ?? '__' }}</div>
        <div class="w-[15%] break-words px-1 text-center">{{ $machine->machine_type ?? '未設定' }}</div>
        <div class="w-[20%] break-words px-1 text-center">
            {{ $machine->storeable->name ?? '無店鋪' }}
        </div>
        <div class="w-[10%] break-words px-1 text-center">{{ $machine->revenue_split }}</div>
        <div class="w-[25%] break-words px-1 text-center font-light">{{ $machine->token }}</div>
        <div class="w-[20%] break-words px-1 text-center flex item-center justify-center">
            @if ($machine->is_active)
                <x-svg-icons name="enable" classes="h-3 w-3" />
            @else
                <x-svg-icons name="disenable" classes="h-3 w-3" />
            @endif
            <form action="{{ route('machine.destroyMachine', $machine) }}" method="POST" class="px-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1 m-auto bg-red-500 text-white rounded-md hover:bg-red-600 transition" onclick="return confirm('確定要刪除此機器嗎？')">
                    <x-svg-icons name="delete" classes="h-4 w-4 " />
                </button>
            </form>
        </div>
    </div>
@endforeach


            </div>
        </div>

        <!-- 新增機器模態框 -->
        <div x-cloak x-show="addMachineModal" class="fixed inset-0 z-50">
            <!-- 背景遮罩 -->
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <!-- 模態框內容 -->
                <div class="p-6 pb-10 bg-white w-full max-w-md rounded-lg shadow-lg" @click.away="addMachineModal = false">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">新增機器</h3>
                    <form action="{{ route('machine.addMachine') }}" method="POST">
                        @csrf
                        <!-- 隱藏擁有者 ID -->
                        <input type="hidden" name="owner_id" value="{{ $ownerId }}">
                        
                        <!-- 金鑰 -->
                        <div class="mb-4">
                            <label for="token" class="block text-sm text-gray-600">金鑰</label>
                            <input type="text" id="token" name="token" class="w-full mt-1 p-2 border rounded-md" required>
                        </div>
                        <!-- 名稱 -->
                        <div class="mb-4">
                            <label for="mname" class="block text-sm text-gray-600">名稱</label>
                            <input type="text" id="mname" name="mname" class="w-full mt-1 p-2 border rounded-md" required>
                        </div>

                        <!-- 機器類型 -->
                        <div class="mb-4">
                            <label for="machine_type" class="block text-sm text-gray-600">機器類型</label>
                            <select id="machine_type" name="machine_type" class="w-full mt-1 p-2 border rounded-md">
                                <option value="pachinko">彈珠台</option>
                                <option value="claw_machine">娃娃機</option>
                                <option value="beat_em_up">打擊遊戲機</option>
                                <option value="racing_game">競速遊戲機</option>
                                <option value="light_gun_game">射擊遊戲機</option>
                                <option value="dance_game">舞蹈機</option>
                                <option value="basketball_game">空中籃球</option>
                                <option value="air_hockey">氣墊球機</option>
                                <option value="slot_machine">電子撲克牌/拉霸機</option>
                                <option value="light_and_sound_game">光明遊戲機</option>
                                <option value="labyrinth_game">迷宮遊戲機</option>
                                <option value="flight_simulator">模擬飛行機</option>
                                <option value="punching_machine">拳擊機</option>
                                <option value="water_shooting_game">水槍射擊遊戲</option>
                                <option value="stacker_machine">堆疊遊戲機</option>
                                <option value="mini_golf_game">高爾夫遊戲機</option>
                                <option value="interactive_dance_game">跳舞/運動互動遊戲機</option>
                                <option value="electronic_shooting_game">電子射擊遊戲機</option>
                                <option value="giant_claw_machine">巨型夾娃娃機</option>
                                <option value="arcade_music_game">大型音樂遊戲機</option>
                            </select>
                        </div>

                        <!-- 分成比例選單 -->
                        <div class="mb-4">
                            <label for="revenue_split" class="block text-sm text-gray-600">分成比例</label>
                            <select id="revenue_split" name="revenue_split" class="w-full mt-1 p-2 border rounded-md">
                                    @for ($i = 0.1; $i <= 0.95; $i += 0.05)
                                        <option value="{{ number_format($i, 2) }}" 
                                            @if (number_format($i, 2) == '0.4') selected @endif>
                                            {{ number_format($i, 2) }}
                                        </option>
                                    @endfor
                            </select>
                        </div>

                        <!-- 確定按鈕 -->
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                確定
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <script>
       // 用於切換下拉菜單顯示
// function toggleDropdown() {
//     const dropdownContent = document.querySelector('.dropdown-content');
//     dropdownContent.classList.toggle('show');
// }

// 用於選擇選項並更新按鈕文本
// function selectOption(storeType) {
//     // 設置隱藏的表單欄位值
//     document.getElementById('arcade_type').value = storeType;
    
//     // 根據選擇更新按鈕的文本
//     const button = document.getElementById('dropdownBtn');
//     if (storeType === 'vsstore') {
//         button.textContent = '已選擇: VSStore';
//     } else if (storeType === 'store') {
//         button.textContent = '已選擇: Store';
//     }
    
//     // 手動提交表單
//     document.getElementById('submitBtn').click();
    
//     // 關閉下拉菜單
//     const dropdownContent = document.querySelector('.dropdown-content');
//     dropdownContent.classList.remove('show');
// }


    </script>
@endsection
@php
    $title = __('msg.machine') . __('msg.list');
@endphp
