{{-- resources/views/arcade/machines/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.machine_management') }}
    </h2>
@endsection

@section('content')
    <div class="py-2" x-data="machineManagement()">
        {{-- 表格或列表顯示所有機器 --}}
        <div class="flex justify-end items-center mb-2 pt-2">
            <button @click="openCreateModal()"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition hover:text-blue-100">
                {{ __('msg.add') }} {{ __('msg.machine') }}
            </button>
        </div>

        <div class="container mx-auto pb-3">
            <div class="bg-white rounded-lg shadow-lg">
                <!-- 標題行 -->
                {{-- <button @click="openEditModal({ id: 1, name: 'Test Machine' })">Test Edit</button> --}}
                <div
                    class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg">
                    <div class="w-[4%] px-1 border-r">#</div>
                    <div class="w-[18%] text-center px-1">
                        <div class="font-thin text-start pe-1"
                            style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ __('msg.owner') }}
                        </div>
                        {{ __('msg.name') }}
                        <div class="font-thin  text-end pe-1"
                            style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ __('msg.creator') }}
                        </div>
                    </div>
                    <div class="w-[23%] text-center" style="line-height: 40px;">{{ __('msg.arcade') }}</div>
                    <div class="w-[15%] text-center" style="line-height: 40px;">{{ __('msg.owner') }}</div>
                    <div class="w-[16%] text-center" style="line-height: 40px;"> {{ __('msg.machine') }} </div>
                    <div class="w-[9%] text-center border-l" style="line-height: 40px;">{{ __('msg.status') }}</div>
                    <div class="w-[15%] flex justify-center items-center" style="line-height: 40px;">
                        {{ __('msg.actions') }}</div>
                </div>
            </div>
            <!-- 數據行 -->
            <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)] py-2">
                @forelse ($machines ?? [] as $machine)
                    <div
                        class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 py-1">
                        <div class="w-[4%] px-1 break-words m-auto border-r">{{ $machine->id }}</div>
                        <div class="w-[18%] px-1 break-words m-auto text-center">
                            <div class="font-thin w-full text-start pe-1" style="font-size: xx-small">
                                <div class="font-thin"
                                    style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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
                            {{ $machine->arcade->name ?? 'Unknown' }}</div>
                        <div class="w-[15%] px-1 break-words m-auto text-center">
                            {{ $machine->owner->name ?? 'Unknown' }}</div>
                        <div class="w-[16%] flex m-auto items-center text-center justify-center">
                            {{ __('msg.' . $machine->machine_type) ?? 'Unknown' }}</div>
                        <div class="w-[9%] flex items-center justify-center m-auto">
                            <form action="{{ route('arcade.machines.toggleActive', $machine->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="owner_id" value="{{ auth()->id() }}">
                                <input type="hidden" name="is_active" value="{{ $machine->is_active ? 0 : 1 }}">
                                <button type="submit">
                                    @if ($machine->is_active)
                                        <x-svg-icons name="statusT" classes="h-6 w-6 inline-block" />
                                    @else
                                        <x-svg-icons name="statusF" classes="h-6 w-6 inline-block" />
                                    @endif
                                </button>
                            </form>
                        </div>
                        <div class="w-[15%] flex items-center justify-center space-x-1 m-auto">
                            <div class="flex justify-items-end space-x-1">
                                {{-- <button
                                    class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                    @click="setEditForm({{ json_encode([
                                        'id' => $machine->id,
                                        'name' => $machine->name,
                                        'owner_id' => $machine->owner_id,
                                        'owner' => $machine->owner ? ['name' => $machine->owner->name] : null,
                                        'machine_type' => $machine->machine_type,
                                        'arcade_id' => $machine->arcade_id,
                                        'chip_hardware_id' => $machine->machineAuthKey?->chip_hardware_id,
                                        'auth_key' => $machine->machineAuthKey,
                                        'payout_type' => $machine->payout_type,
                                        'revenue_split' => $machine->revenue_split,
                                        'coin_input_value' => $machine->coin_input_value,
                                        'payout_unit_value' => $machine->payout_unit_value,
                                        'credit_button_value' => $machine->credit_button_value,
                                        'payout_button_value' => $machine->payout_button_value,
                                        'bill_acceptor_enabled' => $machine->bill_acceptor_enabled,
                                        'bill_currency' => $machine->bill_currency,
                                        'bill_unit_value' => $machine->bill_unit_value,
                                        'balls_per_credit' => $machine->balls_per_credit,
                                        'points_per_credit_action' => $machine->points_per_credit_action,
                                    ]) }})">
                                    <x-svg-icons name="edit" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                </button> --}}
                                <button
                                    class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                    @click="setEditForm({{ json_encode($machine) }})">
                                    <x-svg-icons name="edit" classes="h-4 w-4 sm:h-6 sm:w-6" />
                                </button>


                                <form action="{{ route('arcade.machines.destroy', $machine->id) }}" method="POST"
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
        @include('arcade.machines.partials.create-modal')
        @include('arcade.machines.partials.edit-modal')

    </div>
@endsection

@push('scripts')
    @include('arcade.machines.partials.script')
@endpush
