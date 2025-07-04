
@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Managers') }}
        </h2>
    </x-slot>
    <div class="flex justify-center bg-gray-100 pt-2"  
    x-data="{ editVsStoreModal: false, selectedVsStore: { id: null, name: '', address: '' } , addVsStoreModal: false }">        
    <div class="relative w-full max-w-2xl bg-white bg-opacity-60 shadow-lg rounded-lg">
            <!-- 新增商店按鈕 -->
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <div class="flex items-center p-2 text-lg font-medium text-gray-700">
                    <x-svg-icons name="list" classes="h-6 w-6" />{{ __('msg.vsstore') }}{{ __('msg.list') }}
                </div>
                <button @click="addVsStoreModal = true" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.store') }}
                </button>
            </div>
            
            <!-- 表格 -->
            <div class="shadow-md rounded-lg overflow-scroll p-2" style="max-height: calc(100vh - 80px);">
                <!-- 表頭 -->
                <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                    <div class="w-[30%] px-1 text-center">{{ __('msg.name') }}</div>
                    <div class="w-[40%] px-1 text-center">{{ __('msg.address') }}</div>
                    <div class="w-[30%] px-1 text-center">{{ __('msg.actions') }}</div>
                </div>
                <!-- 商店列表 -->
                @foreach ($vsStores as $vsStore)
                    <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                        <div class="w-[30%] break-words px-1 text-center">{{ $vsStore->name }}</div>
                        <div class="w-[40%] break-words px-1 text-center">{{ $vsStore->address }}</div>
                        <div class="w-[30%] break-words px-1 text-center flex item-center justify-center space-x-2">
                            <button class="px-2 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all duration-200 ease-in-out"
                                @click="editVsStoreModal = true; selectedVsStore = { 
                                    id: {{ $vsStore->id }}, 
                                    name: '{{ $vsStore->name }}', 
                                    address: '{{ $vsStore->address }}', 
                                    phone: '{{ $vsStore->phone }}', 
                                    revenue_split: {{ $vsStore->revenue_split }} 
                                }">
                                <x-svg-icons name="edit" classes="h-4 w-4" />
                            </button>
                            <form action="{{ route('stores.destroyVsStore', $vsStore->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-all duration-200 ease-in-out"
                                    onclick="return confirm('Are you sure?')">
                                    <x-svg-icons name="delete" classes="h-4 w-4" />
                                </button>
                            </form>

                        </div>
                    </div>
                @endforeach
            </div>
            

            <!-- 編輯商店模態框 -->
            <div x-cloak x-show="editVsStoreModal" class="fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                <div class="relative w-full h-full flex items-center justify-center p-4">
                    <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg" @click.away="editVsStoreModal = false">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.edit') }} {{ __('msg.arcade') }}</h2>
                        <div class="p-2"> 
                            <form action="{{ route('machine.updateVsStore') }}" method="POST">
                                @csrf
                                @method('POST')
                                <input type="hidden" name="id" :value="selectedVsStore.id">
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium">{{ __('msg.store') }}{{ __('msg.name') }}</label>
                                    <input type="text" x-model="selectedVsStore.name" name="name" class="w-full border px-2 py-1 rounded-md">
                                </div>
                                <div class="mb-4">
                                    <label for="address" class="block text-sm font-medium">{{ __('msg.store') }}{{ __('msg.address') }}</label>
                                    <input type="text" x-model="selectedVsStore.address" name="address" class="w-full border px-2 py-1 rounded-md">
                                </div>
                                <div class="mb-4">
                                    <label for="phone" class="block text-sm font-medium">{{ __('msg.store') }}{{ __('msg.phone') }}</label>
                                    <input type="text" x-model="selectedVsStore.phone" name="phone" class="w-full border px-2 py-1 rounded-md">
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    {{ __('msg.save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 新增商店模態框 -->
            <div x-cloak x-show="addVsStoreModal" class="fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                <div class="relative w-full h-full flex items-center justify-center p-4">
                    <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg" @click.away="addVsStoreModal = false">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.edit') }} {{ __('msg.cVsArcade') }}</h2>
                        <div class="p-2">   
                            <form action="{{ route('machine.addVsStore') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('msg.store') }}{{ __('msg.name') }}</label>
                                    <input type="text" name="name" id="name" value="vs-store-{{ time() }}" 
                                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                </div>
                                <div class="mb-4">
                                    <label for="address" class="block text-sm font-medium text-gray-700">{{ __('msg.store') }}{{ __('msg.address') }}</label>
                                    <input type="text" name="address" id="address" placeholder={{ __('msg.store') }}{{ __('msg.address') }}
                                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div class="mb-4">
                                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('msg.store') }}{{ __('msg.phone') }}</label>
                                    <input type="text" name="phone" id="phone" placeholder={{ __('msg.store') }}{{ __('msg.phone') }}
                                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div class="flex justify-end mb-4">
                                    <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        {{ __('msg.create') }}{{ __('msg.store') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
@endsection
@php
    $title = '自有商店列表';
@endphp
