{{-- /Users/ilawusong/Documents/sysWawIot/sys180/resources/views/admin/machines/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.machine_management') }} (Admin)
    </h2>
@endsection

@section('content')
    <div class="py-0" x-data="adminMachineManagement()">
        <div class="flex justify-end items-center  px-0">
            <button @click="openCreateModal()"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition hover:text-blue-100">
                {{ __('msg.add') }} {{ __('msg.machine') }}
            </button>
        </div>

        <div class="container mx-auto pb-3">
            <div class="bg-white rounded-lg shadow-lg">
                <!-- Header Row -->
                <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg">
                    <div class="w-[4%] px-1 border-r">#</div>
                    <div class="w-[18%] text-center px-1">{{ __('msg.name') }}</div>
                    <div class="w-[23%] text-center">
                        {{ __('msg.arcade') }}</div>
                    <div class="w-[15%] text-center">{{ __('msg.owner') }}</div>
                    <div class="w-[16%] text-center">{{ __('msg.sort') }}</div>
                    <div class="w-[9%]  text-center border-l">{{ __('msg.status') }}</div>
                    <div class="w-[15%] flex justify-center ">{{ __('msg.actions') }}</div>
                </div>
            </div>
            <!-- Data Rows -->
            <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)] py-2">
                @forelse ($machines as $machine)
                    <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 py-1">
                        <div class="w-[4%]  px-1 break-words m-auto border-r">{{ $machine->id }}</div>
                        <div class="w-[18%] px-1 break-words m-auto text-center">
                            <div class="font-thin w-full text-start pe-1" style="font-size: xx-small">
                                <div
                                    class="ps-2 font-thin text-xs text-start whitespace-nowrap overflow-hidden text-ellipsis">
                                    {{ $machine->owner->name ?? 'Unknown' }}</div>
                            </div>
                            {{ $machine->name }}
                            <div class="font-thin w-full text-end pe-1" style="font-size: xx-small">
                                <div class="font-thin"
                                    style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $machine->creator->name ?? 'Unknown' }}</div>
                            </div>
                        </div>
                        <div class="w-[23%] px-1 break-words m-auto text-center">
                            <div class="ps-2 font-thin text-xs text-center whitespace-nowrap overflow-hidden text-ellipsis">
                                {{ $machine->arcade->share_pct ?? 'Unknown' }}</div>
                            {{ $machine->arcade->name ?? 'Unknown' }}
                        </div>

                        <div class="w-[15%] px-1 break-words m-auto text-center">
                            <div class="ps-2 font-thin text-xs text-center whitespace-nowrap overflow-hidden text-ellipsis">
                                {{ $machine->share_pct ?? 'Unknown' }}</div>
                            {{ $machine->owner->name ?? 'Unknown' }}
                        </div>
                        <div class="w-[16%] flex m-auto justify-center">
                            {{ __('msg.' . $machine->machine_type) ?? 'Unknown' }}</div>
                        <div class="w-[9%] flex items-center justify-center m-auto">
                            <form action="{{ route('admin.machines.toggleActive', $machine->id) }}" method="POST"
                                class="flex justify-items-end space-x-1">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $machine->is_active ? 0 : 1 }}">
                                <button type="submit"
                                    onclick="return confirm('{{ __('msg.confirm_active') }} {{ $machine->is_active ? __('msg.deactivate') : __('msg.activate') }}{{ __('msg.this_machine') }}{{ __('msg.zh_ask') }}')"
                                    class="m-auto text-white transition p-1">
                                    @if ($machine->is_active)
                                        <x-svg-icons name="statusT" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                    @else
                                        <x-svg-icons name="statusF" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                    @endif
                                </button>
                            </form>
                        </div>
                        <div class="w-[15%] flex items-center justify-center space-x-1 m-auto">
                            <!-- 編輯按鈕 -->
                            <div class="flex justify-items-end space-x-1">
                                <button
                                    class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                    @click="openEditModal({{ json_encode(
                                        [
                                            'id' => $machine->id,
                                            'name' => $machine->name ?? '',
                                            'machine_type' => $machine->machine_type ?? '',
                                            'arcade_id' => $machine->arcade_id ?? '',
                                            'arcade' => $machine->arcade
                                                ? [
                                                    'currency' => $machine->arcade->currency ?? 'TWD',
                                                ]
                                                : null,
                                            'machine_auth_key' => $machine->machineAuthKey
                                                ? [
                                                    'auth_key' => $machine->machineAuthKey->auth_key ?? '',
                                                    'chip_hardware_id' => $machine->machineAuthKey->chip_hardware_id ?? '',
                                                ]
                                                : null,
                                            'owner_id' => $machine->owner_id ?? '',
                                            'owner' => $machine->owner
                                                ? [
                                                    'name' => $machine->owner->name ?? 'Unknown',
                                                ]
                                                : null,
                                            'coin_input_value' => $machine->coin_input_value ?? '0',
                                            'credit_button_value' => $machine->credit_button_value ?? '0',
                                            'payout_button_value' => $machine->payout_button_value ?? '0',
                                            'payout_type' => $machine->payout_type ?? 'none',
                                            'payout_unit_value' => $machine->payout_unit_value ?? '0',
                                            'revenue_split' => $machine->revenue_split ?? 45,
                                            'bill_acceptor_enabled' => $machine->bill_acceptor_enabled ?? false,
                                            'bill_currency' => $machine->bill_currency ?? 'TWD',
                                            'share_pct' => $machine->share_pct, // 直接傳遞資料庫的值
                                            'accepted_denominations' => $machine->accepted_denominations ?? [],
                                        ],
                                        JSON_HEX_QUOT | JSON_HEX_APOS,
                                    ) }})">
                                    <x-svg-icons name="edit" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                </button>
                                <form action="{{ route('admin.machines.destroy', $machine->id) }}" method="POST"
                                    class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <x-button
                                        class="bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto"
                                        type="submit"
                                        onclick="return confirm('{{ __('msg.confirm_delete') }}{{ __('msg.zh_ask') }}')">
                                        <x-svg-icons name="delete" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-gray-500">
                        {{ __('msg.no_machines_found') }}
                    </div>
                @endforelse
            </div>
        </div>
        {{-- Include Modals --}}
        @include('admin.machines.partials.create-modal')
        @include('admin.machines.partials.edit-modal')
    </div>
@endsection

@push('scripts')
    {{-- Include the Admin specific Alpine script --}}
    @include('admin.machines.partials.script')
@endpush

@php
    $title = __('msg.machine_management');
@endphp
