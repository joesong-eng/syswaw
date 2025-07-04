@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Store Machine Management') }}
        </h2>
    </x-slot>

    <div class="flex justify-center bg-gray-100 pt-2"  
        x-data="{ addMachineModal: false }">
        <div class="relative w-full bg-white bg-opacity-60 shadow-lg rounded-lg">
            <!-- Add New Manager 標題和按鈕 -->
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <div class="flex items-center p-2 text-lg font-medium text-gray-700">
                    <x-svg-icons name="list" classes="h-6 w-6" />{{__('msg.chip_token')}}
                </div>
                <button @click="addMachineModal = true" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.chip_token') }}
                </button>
            </div>

            <!-- 篩選機主 -->
            <div class="relative pb-6 bg-white rounded-lg shadow hover:bg-gray-50 ">
                <form class="flex justify-end" action="{{ route('stores.machine_key_management') }}" method="GET">
                    <div class="flex">
                        <div class="flex flex-col items-center px-3 py-3">
                            {{-- <select name="owner_id" id="owner_id" class="block md:w-48 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" 
                            onchange="showLoadingOverlay(); this.form.submit()">
                            <option value="" class="text-sm">所有機主</option>
                            @foreach ($storeOwners as $owner)
                                <option value="{{ $owner->id }}" {{ isset($filterOwner) && $filterOwner == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }}
                                </option>
                            @endforeach
                        </select> --}}
                        </div>
                    </div>
                </form>
                <!-- Header Row -->
                <div class="flex items-center m-1 border-b border-gray-200 text-sm font-medium text-gray-700 px-1" >
                    <div class="w-[4%]  px-1">ID</div>
                    <div class="w-[34%] px-1 text-center border-r-2">{{__('msg.token')}}</div>
                    <div class="w-[14%] px-1 text-center border-r-2">{{__('msg.expires_at')}}</div>
                    <div class="w-[11%] px-1 text-center border-r-2">{{__('msg.status')}}</div>
                    <div class="w-[14%] px-1 text-center border-r-2">{{__('msg.arcade')}}</div>
                    <div class="w-[14%] px-1 text-center border-r-2">{{__('msg.creator')}}</div>
                    <div class="w-[9%]  px-1 text-center border-r-2">{{__('msg.actions')}}</div>
                </div>

                <!-- Data Rows -->
                <div 
                class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]"
                >
                    @foreach ($machines as $machine)
                    <div class="flex items-center m-1 border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                        <div class="w-[4%]  px-1 ">{{ $loop->iteration }}</div>
                        <div class="w-[34%] px-1 break-words cursor-copy text-center" onclick="copyToClipboardAndGenerateLink('{{ $machine->token }}','chip')">{{ $machine->token }}</div>
                        <div class="w-[14%] px-1 break-words">{{ \Carbon\Carbon::parse($machine->expires_at)->format('ymd/hi') }}</div>
                        <div class="w-[11%] px-1 text-center">
                            @if ($machine->used)
                            <span class="text-red-500">已使用</span>
                            @else
                            <span class="text-green-500">未使用</span>
                            @endif</div>
                        <div class="w-[14%] px-1 text-center">
                            @if ($machine->storeable_type === 'App\Models\VsStore')
                                {{ $vsStores->firstWhere('id', $machine->storeable_id)?->name ?? '未知虛擬店鋪' }}
                            @elseif ($machine->storeable_type === 'App\Models\Store')
                                {{ $stores->firstWhere('id', $machine->storeable_id)?->name ?? '未知實體店鋪' }}
                            @endif</div>
                        <div class="w-[14%] px-1 break-words text-center">{{ $machine->creator->name ?? 'Unknown' }}</div>
                        <div class="w-[9%]  px-1 text-right">
                            <form action="{{ route('machine.destroyMachineKey', $machine) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                                    onclick="return confirm('確定要刪除此金鑰嗎？')">
                                    <x-svg-icons name="delete" classes="h-6 w-6" />
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                
                </div>
            </div>

        </div>

        <!-- 新增機器金鑰模態框 -->
        <div x-cloak x-show="addMachineModal" class="fixed inset-0 z-50">
            <!-- 背景遮罩 -->
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <!-- 模態框內容 -->
                <div class="p-6 pb-10 relative bg-white w-full max-w-md rounded-lg shadow-lg" @click.away="addMachineModal = false">
                    <!-- 標題 -->
                    <div class="flex items-center py-2 text-lg font-medium text-gray-700 ">
                        <x-svg-icons name="token" classes="h-6 w-6" />
                        {{ __('新增機器金鑰') }}
                    </div>
                    
                    <!-- 表單 -->
                    <form action="{{ route('machine.addMachinekey') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="owner_id" value="{{ $ownerId }}" readonly />
                        <div class="mb-4">
                            <input type="text" name="storeable_type" id="storeable_type" value="">                            
                            <input type="hidden" name="create_by" id="create_by" value="{{ Auth::user()->id }}">                            
                        </div>
                        <!-- 機器管理員 -->
                        <div class="flex justify-between mb-4">
                            <div>
                                <label for="count" class="block text-sm font-medium text-gray-700">數量</label>
                                <input type="number" name="count" id="count" value="1" min="1" max="50" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required/>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition font-medium h-10">新增</button>
                            </div>
                        </div>
                        <label for="storeable_id" class="block text-sm font-medium text-gray-700">
                            選擇店鋪 / <a href="{{ route('machine.visualStore') }}" class="text-blue-500 hover:text-blue-100 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">新增自定義店鋪</a>
                        </label>
                        <select name="storeable_id" id="storeable_id" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            onchange="updateStoreType()" required>
                            <optgroup label="虛擬店鋪 (Virtual Store)">
                                @foreach ($stores as $store)
                                <option value="{{ $store->id }}" data-type="App\Models\Store">{{ $store->name }}</option>
                                @endforeach
                                @foreach ($vsStores as $vsStore)
                                <option value="{{ $vsStore->id }}" data-type="App\Models\VsStore">自-{{ $vsStore->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function updateStoreType() {
            const select = document.getElementById('storeable_id'); // 獲取 select 元素
            const selectedOption = select.options[select.selectedIndex]; // 獲取選中的 option
            const storeType = selectedOption.getAttribute('data-type'); // 獲取 data-type 屬性值
            document.getElementById('storeable_type').value = storeType; // 更新隱藏的 input 值
        }
        document.addEventListener('DOMContentLoaded', function () {
            updateStoreType();
        }, { passive: true });
    </script>
@endsection
@php
    $title = '新增機器金鑰';
@endphp

