<!-- resources/views/arcades/index.blade.php -->
@extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900 pt-2"
         x-data="{ editArcadeModal: false, selectedStore: {} }">
        <div class="relative w-full max-w-2xl bg-opacity-60 dark:bg-gray-800 dark:bg-opacity-70 px-6 overflow-scroll" style="max-height: calc(100vh - 70px);">
            @foreach ($arcades as $arcade)
            <div class="bg-white border border-gray-200 rounded-lg md:max-w-2xl dark:border-gray-700 dark:bg-gray-800 shadow-lg">
                <div class="flex px-3 pt-3 justify-between">
                    <div class="flex justify-items-start font-thin pb-0 m-0 text-gray-700 dark:text-gray-400">
                        {{ __('狀態: ') }} {{ $arcade->is_active ? '啟用' : '停用' }}
                    </div>
                    <div class="flex justify-items-end space-x-1">
                        <button class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition px-2"
                            @click="editArcadeModal = true; selectedStore = { 
                                id: {{ $arcade->id }}, 
                                name: '{{ $arcade->name }}', 
                                image_url: '{{ $arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg') }}', 
                                image_name: '{{ $arcade->image_url ? $arcade->image_url :('default-store.jpg') }}', 
                                address: '{{ $arcade->address }}', 
                                phone: '{{ $arcade->phone }}', 
                                owner_name: '{{ $arcade->owner->name }}', 
                                owner_id: '{{ $arcade->owner_id }}', 
                                business_hours: '{{ $arcade->business_hours }}', 
                                revenue_split: {{ $arcade->revenue_split }} 
                            }">
                            <x-svg-icons name="edit" classes="h-4 w-4" />
                        </button>
                        <!-- 顯示 selectedStore.image_url -->

                        <form action="{{ route('arcade.destroy', $arcade->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:underline bg-red-500 text-white rounded-md hover:bg-red-600 transition p-2" 
                            onclick="return confirm('{{__('msg.confirm_active')}} {{ __('msg.delete') }}{{__('msg.this_arcade')}}{{__('msg.zh_ask')}}')" class=" text-white transition p-1"
                            >
                                <x-svg-icons name="delete" classes="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row">
                    @if (Auth::user()->arcade || Auth::user()->parent->arcade)
                        <div class="m-auto w-full max-w-96">
                            <!-- 店鋪圖片 -->
                            <img id="storeImage_{{ $arcade->id }}" 
                                x-bind:src="selectedStore.id === {{ $arcade->id }} ? selectedStore.image_url : '{{ $arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg') }}'"
                                class="m-auto rounded-md dynamic-style h-full max-h-40 py-1">
                        </div>
                        <div class="flex flex-col justify-between p-4 leading-normal w-full">
                            <div class="flex items-center m-auto justify-between p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $arcade->name }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">地址: {{ $arcade->address ?? '未設定' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">電話: {{ $arcade->phone ?? '未設定' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                    <div class="flex flex-col justify-between p-4 leading-normal w-full">
                        <p class="text-gray-700 dark:text-gray-400">
                            {{ __('您尚未設置店鋪資訊。') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <x-modal.arcade-edit :arcades="$arcades"/>
    </div>
@endsection
@php
    $title = __('msg.arcade_info');
@endphp