{{-- ArcadesController/arcades: : admin/index --}}
@extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900" 
        x-data="{ editArcadeModal: false, selectedStore: {}, addStoreModal: false,arcadeOwners:  {{$arcadeOwners}}  }">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    {{ __('msg.arcade_management') }}
                </h2>
                <x-button >
                    <a href="/admin/arcade/create">{{__('msg.create_arcade')}}</a>
                </x-button>
            </div>
            <!-- 商店列表 -->
            <div class="container mx-auto px-2 pb-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-start border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg">
                        <div class="w-[19%] items-center text-center"> {{ __('msg.name') }} </div>
                        <div class="w-[30%] items-center text-center"> {{ __('msg.address') }} </div>
                        <div class="w-[17%] items-center text-center"> {{ __('msg.owner') }} </div>
                        <div class="w-[10%] items-center text-center"> {{ __('msg.type') }} </div>
                        <div class="w-[9%] items-center text-center">  {{ __('msg.status') }} </div>
                        <div class="w-[15%] items-center text-center"> {{ __('msg.active') }} </div>
                    </div>

                    <!-- Data Rows -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)] py-2 ">
                        @foreach ($arcades as $arcade)
                        <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100 py-1">
                                <div class="w-[19%] break-words text-center">{{ $arcade->name }}</div>
                                <div class="w-[30%] break-words text-center">{{ $arcade->address }}</div>
                                <div class="w-[17%] break-words text-center">{{ $arcade->owner->name ?? 'N/A' }}</div>
                                <div class="w-[10%] break-words text-center">{{ $arcade->type ?? 'N/A' }}</div>
                                <div class="w-[9%]  justify-items-center text-center m-auto space-x-1 border-l">
                                    <form action="{{ route('admin.arcade.toggleActive', $arcade->id) }}" method="POST" 
                                        class="flex justify-items-center space-x-1"
                                        onsubmit="return confirm('Are you sure you want to {{ $arcade->is_active ? 'deactivate' : 'activate' }} this arcade?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $arcade->is_active ? 0 : 1 }}">
                                        <button type="submit" 
                                            class="m-auto text-white transition p-1">
                                            @if ($arcade->is_active)
                                                <x-svg-icons name="statusT" classes="h-6 w-6" />
                                            @else   
                                                <x-svg-icons name="statusF" classes="h-6 w-6" />
                                            @endif
                                        </button>
                                    </form></div>
                                <div class="w-[15%] break-words justify-items-center text-center m-auto space-x-1">
                                    <!-- 編輯按鈕 -->
                                    <div class="flex justify-items-between space-x-1">
                                        <x-button class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
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
                                            <x-svg-icons name="edit" classes="h-4 w-4" /></x-button>
                                        <form action="{{ route('arcade.destroy', $arcade->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <x-button  
                                                class="bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto" 
                                                type="submit" onclick="return confirm('{{__('msg.confirm_active')}} {{ __('msg.delete') }}{{__('msg.this_machine')}}{{__('msg.zh_ask')}}')">
                                                <x-svg-icons name="delete" classes="h-4 w-4" />
                                            </x-button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <x-modal.arcade-edit :arcadeOwners="$arcadeOwners"/>
    </div>
@endsection
@php
    $title = __('msg.arcade_management');
@endphp

