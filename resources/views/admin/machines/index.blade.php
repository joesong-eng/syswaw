{{-- admin/machines/index --}}
</index>@extends('layouts.app')
@section('content')
    @if (session('success'))
    <div id="success-message" class="absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg  duration-1000 ease-out transform transition-transform slide-in">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="absolute message mb-4 p-4 text-red-800 bg-red-100 border border-red-200 rounded-lg slide-in">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900 pt-2" 
        x-data="{ }"
    >
        <div class="relative w-full max-w-4xl bg-white bg-opacity-60 dark:bg-gray-800 dark:bg-opacity-70 shadow-lg rounded-lg">            
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('msg.machine_owner') }}{{ __('msg.list') }}</h2>
                <form action="{{ route('admin.machines') }}" method="GET">
                    <div class="flex items-center space-x-2">
                        <label for="owner_id" class="text-sm font-medium text-gray-700 dark:text-gray-100"></label>
                        {{-- 篩選完要記住所選擇的項目 --}}
                        <select name="owner_id" id="owner_id" class="form-select block w-full md:w-48 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="this.form.submit()">
                            <option value="">{{ __('msg.all') }}</option>
                            @foreach ($machineOwners as $machineOwner)
                                <option value="{{ $machineOwner->id }}" @if ($machineOwner->id == request('owner_id')) selected @endif>
                                    {{ $machineOwner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>


            <!-- 機器列表 -->
            <div class="shadow-md rounded-lg overflow-scroll p-2">
                @if ($machines->isEmpty())
                <p class="text-center text-gray-500 dark:text-gray-400">尚未找到任何機台資料。</p>
            @else
                @foreach ($machines as $machine)
                    <div class="flex items-center m-4 border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100">
                        <div class="w-[20%] break-words">
                            {{ $machine->owner->id ?? 'N/A' }} {{ $machine->owner->name ?? '未指定' }}
                        </div>
                        <div class="w-[25%] break-words">{{ $machine->owner->email ?? '未提供' }}</div>
                        <div class="w-[15%] flex min-h-full border-r-2">
                            {{ $machine->owner ? ($machine->owner->roles->first()?->name ?? '無角色') : '無角色' }}
                        </div>
                        <div class="w-[10%] flex items-center justify-center space-x-2">
                            @if ($machine->owner->is_active ?? false)
                                <x-svg-icons name="statusT" classes="h-6 w-6" />
                            @else
                                <x-svg-icons name="statusF" classes="h-6 w-6" />
                            @endif
                        </div>
                        <div class="w-[20%] truncate" title="{{ $machine->token }}">{{ $machine->token }}</div>
                        <div class="w-[10%] flex items-center justify-center space-x-2">
                            {{-- <a href="{{ route('admin.machines.edit', $machine->id) }}" class="inline-block px-1 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                <x-svg-icons name="edit" classes="h-6 w-6" />
                            </a> --}}
                            {{-- <form action="{{ route('admin.machines.destroy', $machine->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-1 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition" onclick="return confirm('確定要刪除嗎？')">
                                    <x-svg-icons name="delete" classes="h-6 w-6" />
                                </button>
                            </form> --}}
                        </div>
                    </div>
                @endforeach
            @endif

            </div>
        </div>
    </div>
@endsection
@php
    $title = __('msg.machine_management');
@endphp
