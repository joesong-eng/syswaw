@extends('layouts.app')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Managers') }}
        </h2>
    </x-slot>
    <div class="flex justify-center bg-gray-100  h-full"  
        x-data="{ editManagerModal: false, selectedManager: {}, addManagerModal: false }">        
        <div class="relative w-full max-w-2xl bg-white bg-opacity-60 shadow-lg rounded-lg">
            <!-- Add New Manager 標題和按鈕 -->
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <div class="flex items-center p-2 text-lg font-medium text-gray-700">
                    <x-svg-icons name="list" classes="h-6 w-6" />{{ __('msg.staff') }}{{ __('msg.management') }}
                </div>
                <button @click="addManagerModal = true" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    {{ __('msg.create') }}{{ __('msg.staff') }}
                </button>
            </div>

            {{-- <div class="flex justify-end px-3 pt-3">
                {{ __('msg.staff') }}{{ __('msg.management') }}
                <div class="flex flex-col place-items-end w-full space-y-3 md:space-y-0 md:space-x-1">
                    <div class="flex justify-end w-full max-w-40"> 
                        <button @click="addManagerModal = true" class="px-4 py-2 w-2/3 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            {{ __('msg.create') }}{{ __('msg.staff') }}
                        </button>
                    </div>
                </div>
            </div> --}}
            <div class="shadow-md rounded-lg overflow-scroll p-2" style="max-height: calc(100vh - 80px);">
                <!-- 表頭 -->
                <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                    <div class="w-[30%] px-1 text-center">{{ __('msg.name') }}</div>
                    <div class="w-[40%] px-1 text-center">{{ __('msg.email') }}</div>
                    <div class="w-[30%] px-1 text-center">{{ __('msg.actions') }}</div>
                </div>
                <!-- 員工列表 -->
                @foreach ($managers as $manager)
                    <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 p-2">
                        <div class="w-[30%] break-words px-1 text-center">{{ $manager->name }}</div>
                        <div class="w-[40%] break-words px-1 text-center">{{ $manager->email }}</div>
                        <div class="w-[30%] break-words px-1 text-center flex item-center justify-center space-x-2">
                        <button class="px-2 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all duration-200 ease-in-out"
                        @click="editManagerModal = true; selectedManager = { id: {{ $manager->id }}, name: '{{ $manager->name }}', email: '{{ $manager->email }}' }">
                            <x-svg-icons name="edit" classes="h-4 w-4" />
                        </button>
                        <form action="{{ route('stores.destroyVsStore', $manager->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-all duration-200 ease-in-out"
                            onclick="return confirm('{{ __('msg.confirm_delete') }}');">
                            <x-svg-icons name="delete" classes="h-4 w-4" />
                            </button>
                        </form>


                        </div>
                    </div>
                @endforeach
            </div>
            
            

            <!-- 編輯管理員模態框 -->
            <div x-cloak x-show="editManagerModal" class="fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                <div class="relative w-full h-full flex items-center justify-center p-4">
                    <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg" @click.away="editManagerModal = false">
                        <h2 class="text-lg font-semibold mb-4 text-gray-800">{{ __('msg.edit') }} {{ __('msg.staff') }}</h2>
                        <div class="p-2">   
                            <form :action="`/machine/updateManager/${selectedManager.id}`" method="POST">
                                @csrf
                                @method('POST')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-800">{{__('msg.name')}}</label>
                                    <input type="text" x-model="selectedManager.name" name="name" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-800">{{__('msg.email')}}</label>
                                    <input type="email" x-model="selectedManager.email" name="email" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-800">{{__('msg.password')}} ({{ __('msg.Optional') }})</label>
                                    <input type="password" x-model="selectedManager.password" name="password" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-800">{{__('msg.confirm')}} {{__('msg.password')}}</label>
                                    <input type="password" x-model="selectedManager.password_confirmation" name="password_confirmation" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                                <div class="flex justify-end mt-4">
                                    <button type="button" class="px-4 py-2 mr-2 bg-gray-300 rounded-md hover:bg-gray-400" @click="editManagerModal = false">{{__('msg.cancel')}}</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">{{__('msg.save')}}</button>
                                </div>
                            </form>
                    </div>
                    </div>
                </div>
            </div>
            
            <!-- 新增管理員模態框 -->
            <x-modal.staff-create-modal :staff="$staff" />

        </div>
    </div>
@endsection
@php
    $title = __('msg.staff_management');
@endphp
