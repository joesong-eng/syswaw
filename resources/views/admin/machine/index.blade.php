@extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900" 
        x-data="{ editMachineModal: false, selectedMachine: {}, addMachineModal: false, storeableType: '' }">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    {{ __('msg.machine_management') }}
                </h2>
                <button @click="addMachineModal = true" class="font-semibold rounded-md shadow px-4 py-2 text-gray-200 hover:text-gray-500 bg-blue-500 hover:bg-blue-700 transition">
                    {{ __('msg.add')}}{{__('msg.machine') }}
                </button>
            </div>
            
            <div class="container mx-auto px-2 pb-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-start border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg">
                        <div class="w-[4%] px-1 border-r">#</div>
                        <div class="w-[18%] text-center px-1">{{__('msg.name')}}</div>
                        <div class="w-[23%] text-center">{{__('msg.arcade')}}</div>
                        <div class="w-[15%] text-center">{{__('msg.owner')}}</div>
                        <div class="w-[16%] text-center">{{__('msg.sort')}}</div>
                        <div class="w-[9%]  text-center border-l">{{__('msg.status')}}</div>
                        <div class="w-[15%] flex justify-center ">{{__('msg.actions')}}</div>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)] py-2">
                    @foreach ($machines as $machine)
                    <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100 py-1">
                        <div class="w-[4%]  px-1 break-words m-auto border-r">{{ $machine->id }}</div>
                        <div class="w-[18%] px-1 break-words m-auto text-center">
                            <div class="font-thin w-full text-start pe-1" style="font-size: xx-small">
                                <div class="font-thin" style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $machine->owner->name ?? 'Unknown' }}</div>
                            </div>
                            {{ $machine->name }}
                            <div class="font-thin w-full text-end pe-1" style="font-size: xx-small">
                                <div class="font-thin" style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $machine->creator->name ?? 'Unknown' }}</div>
                            </div>
                        </div>
                        <div class="w-[23%] px-1 break-words m-auto text-center">{{ $machine->arcade->name ?? 'Unknown' }}</div>
                        <div class="w-[15%] px-1 break-words m-auto text-center">{{ $machine->owner->name ?? 'Unknown' }}</div>
                        <div class="w-[16%] flex m-auto justify-center">{{ __('msg.'.$machine->machine_type) ?? 'Unknown' }}</div>
                        <div class="w-[9%] flex items-center justify-center m-auto">
                            <form action="{{ route('machine.toggleActive', $machine->id) }}" method="POST" 
                                class="flex justify-items-end space-x-1" >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $machine->is_active ? 0 : 1 }}">
                                <button type="submit" 
                                    onclick="return confirm('{{__('msg.confirm_active')}} {{ $machine->is_active ? __('msg.deactivate') : __('msg.activate') }}{{__('msg.this_machine')}}{{__('msg.zh_ask')}}')" 
                                    class="m-auto text-white transition p-1">
                                    @if ($machine->is_active)
                                        <x-svg-icons name="statusT" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                    @else   
                                        <x-svg-icons name="statusF" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                    @endif
                                </button>
                            </form></div>
                        <div class="w-[15%] flex items-center justify-center space-x-1 m-auto">
                            <!-- 編輯按鈕 -->
                            <div class="flex justify-items-end space-x-1">
                                {{-- <x-button class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                    @click="editMachineModal = true; selectedMachine = {{ $machine }}; storeableType = selectedMachine.storeable_type" 
                                    >
                                    <x-svg-icons name="edit" classes="h-4 w-4" />
                                </x-button> --}}
                                <x-button class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                    @click="editMachineModal = true; selectedMachine = {{ $machine->toJson() }}">
                                    <x-svg-icons name="edit" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                </x-button>
                                <form action="{{ route('machine.destroy', $machine->id) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <x-button 
                                        class="bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto" 
                                        type="submit" onclick="return confirm('{{__('msg.confirm_delete')}}{{__('msg.zh_ask')}}')">
                                        <x-svg-icons name="delete" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <x-modal.machine-create :arcades="$arcades" :users="$users" />
        <x-modal.machine-edit :arcades="$arcades" :users="$users"/>
    </div>
@endsection
@php
    $title = __('msg.machine_management');
@endphp