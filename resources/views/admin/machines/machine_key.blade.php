{{-- stores.machine_key 新增機台金鑰 --}}
@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Store Machine Management') }}
        </h2>
        {{-- @if (session('success'))
            <div id="success-message" class="message mb-4 p-4 text-green-800 bg-green-100 border border-green-200 rounded-lg">
                {{ session('success') }} </div>
        @endif
        @if ($errors->any())
            <div id="errors_msg" class="mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button onclick="document.getElementById('errors_msg').remove();"
                    class="absolute top-0 bottom-0 right-0 px-4 py-3 text-yellow-700 hover:text-yellow-900">X </button>
            </div>
        @endif --}}
    </x-slot>
    <div class="flex justify-center bg-gray-100 pt-2" x-data="{ editChipModal: false, selectedChip: {}, addOwnerModal: false }">
        <div class=" w-full max-w-6xl bg-white bg-opacity-60 shadow-lg rounded-lg p-2">
            <!-- 篩選機主 -->
            {{-- <form action="{{ route('printKeys') }}" method="POST" id="printForm">
                @csrf --}}
            <!-- 篩選與列表標題區塊保持不變 -->
            <div
                class="relative pt-4 bg-white rounded-lg shadow hover:bg-gray-50 overflow-scroll ">
                <!-- 標題與按鈕 -->
                <div class="flex justify-between">
                    <!-- 左邊標題 (小螢幕隱藏, sm 以上顯示) -->
                    <div name="elem1" class="hidden sm:!flex items-center w-2/5">
                        <x-svg-icons name="list" classes="h-6 w-6" />
                        <span class="text-lg font-thin">{{ __('機器金鑰列表') }}</span>
                    </div>

                    <!-- 右側按鈕與選單 -->
                    <div class="flex w-full sm:w-auto space-y-3 sm:space-y-0 sm:space-x-1">
                        <!-- 新增金鑰按鈕 (小螢幕靠右) -->
                        <button name="elem2-2"
                            class="bg-blue-500 order-2 w-full sm:w-auto sm:ml-auto max-w-40 px-4 py-2 text-white rounded-md hover:bg-blue-600"
                            type="button" @click="addOwnerModal = true">
                            新增金鑰
                        </button>
                        <!-- 下拉選單 (始終顯示) -->
                        <div name="elem2-1" class="flex w-full sm:w-auto order-1">
                            <form name="elem3" class="w-full"
                                action="{{ Auth::user()->hasRole('admin') ? route('admin.machineKey') : route('aracdes.machineKey') }}"
                                method="GET">
                                @csrf
                                <select name="owner_id" id="owner_id"
                                    class="block w-full md:w-48 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    onchange="showLoadingOverlay();  this.form.submit()">
                                    <option value="" {{ empty($filterOwner) ? 'selected' : '' }}>
                                        {{ Auth::user()->hasRole('admin') ? '所有機主' : '店內所屬' }}
                                    </option>
                                    @foreach ($storeOwners as $owner)
                                        <option value="{{ $owner->id }}"
                                            {{ $filterOwner == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>


                    </div>
                </div>

                <hr class="m-2 shadow-lg">
                <!-- 列印按鈕與列表標題 -->
                <div
                    class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 px-1">
                    <button type="button" id="printButton"
                        class="w-[6%] qrcodeprint px-1 text-center text-xs items-center rounded-lg p-2 bg-yellow-200 hover:bg-yellow-300"
                        onclick="handlePrintClick()">
                        列印
                    </button>
                    <div class="w-[20%] px-1 text-center text-sm">id 金鑰</div>
                    <div class="w-[10%] px-1 text-sm">過期時間</div>
                    <div class="w-[14%] px-1 text-center text-sm">商店</div>
                    <div class="w-[22%] px-1 text-center text-sm">歸屬|製表</div>
                    <div class="w-[12%] px-1 text-center text-sm">使用狀態</div>
                    <div class="w-[16%] px-1 text-center text-sm">操作</div>
                </div>
                <!-- 列表資料部分 -->
                <div class="shadow-lg rounded-sm bg-gray-200" style="max-height: calc(100vh - 180px);">
                    @foreach ($machines as $machine)
                        <div
                            class="flex items-center border-b border-gray-200 text-sm font-thin text-gray-700 space-x-1">
                            <div class="w-[6%] break-words text-center">
                                <input type="checkbox" name="selected_ids[]" value="{{ $machine->id }}"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                            </div>
                            <div class="w-[20%] break-words cursor-copy text-center" id="machineToken_{{ $machine->id }}"
                                {{-- Add unique ID --}}
                                onclick="copyToClipboard('{{ $machine->token }}','machineToken_{{ $machine->id }}')">
                                {{-- Use new function --}}
                                {{ $loop->iteration }}.{{ $machine->token }}
                            </div>
                            <div class="w-[10%] break-words text-sm">
                                {{ \Carbon\Carbon::parse($machine->expires_at)->format('ymd/Hi') }}
                            </div>
                            <div class="w-[14%] break-words text-center">
                                @if ($machine->storeable_type === 'App\Models\VsStore')
                                    {{ $vsStores->firstWhere('id', $machine->storeable_id)?->name ?? '未知虛擬店鋪' }}
                                @elseif ($machine->storeable_type === 'App\Models\Store')
                                    {{ $stores->firstWhere('id', $machine->storeable_id)?->name ?? '未知實體店鋪' }}
                                @endif
                            </div>
                            <div class="w-[22%] break-words text-center">
                                {{ $machine->owner ? $machine->owner->name : '未指定' }}|{{ $machine->creator->name ?? 'Unknown' }}
                            </div>
                            <div class="w-[12%] break-words text-center">
                                @if ($machine->used)
                                    <span class="text-red-500 text-sm font-thin break-words">已使用</span>
                                @else
                                    <span class="text-green-500 text-sm font-thin break-words">未使用</span>
                                @endif
                            </div>
                            <div class="w-[16%] break-words text-right flex space-x-1">
                                <div class="inline-block items-center">
                                    @if ($machine->is_active)
                                        <x-svg-icons name="statusT" classes="h-4 w-4 items-center" />
                                    @else
                                        <x-svg-icons name="statusF" classes="h-4 w-4 items-center" />
                                    @endif
                                </div>
                                <button type="button"
                                    class="inline-block bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
                                    @click="editChipModal = true; selectedChip={
                                            id: {{ $machine->id }}, 
                                            owner_id: {{ $machine->owner_id ?? 'null' }}, 
                                            storeable_id: {{ $machine->storeable_id }},
                                            storeable_type: '{{ $machine->storeable_type }}', 
                                            machine_id: '{{ $machine->machine_id }}',
                                            name: '{{ $machine->name }}',
                                            type: '{{ $machine->machine_type }}',
                                            is_active: {{ $machine->is_active }},
                                            revenue_split: {{ $machine->revenue_split }},
                                            token: '{{ $machine->token }}'
                                        }">
                                    <x-svg-icons name="edit" classes="h-4 w-4" />
                                </button>
                                <form action="{{ route('stores.destroyKey', $machine) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 text-white rounded-md hover:bg-red-600 transition p-1"
                                        onclick="return confirm('確定要刪除此金鑰嗎？')">
                                        <x-svg-icons name="delete" classes="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- </form> --}}
        </div>
        <!-- 新增機器金鑰模態框 -->
        <x-modal.machine-key :storeOwners="$storeOwners" :stores="$stores" :currentUser="$users" />
        <x-modal.chip-key :stores="$stores" :vsStores="$vsStores" />
    </div>
@endsection
@php
    $title = __('msg.machine_key') . __('msg.management');
@endphp
