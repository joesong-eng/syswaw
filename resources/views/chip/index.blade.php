@extends('layouts.app')
@section('content')
    <div class="flex justify-center bg-gray-100">
        <div class="relative w-full bg-white bg-opacity-60 shadow-lg rounded-lg">
            <div class="flex justify-end items-center mb-2 px-6 pt-2">
                <h2 class="text-lg font-semibold text-gray-800 mx-4">
                    {{ __('msg.token_management') }}
                </h2>
                <div class="flex items-center">
                    <form id="filterForm" action="{{ route('arcade.chips.index') }}" method="GET" class="mr-4">
                        {{-- 修正路由名稱 --}}
                        <select name="filter" id="filter" class="border rounded-md px-2 py-1"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>{{ __('msg.all') }}</option>
                            <option value="used" {{ $filter == 'used' ? 'selected' : '' }}>{{ __('msg.used') }}</option>
                            <option value="unused" {{ $filter == 'unused' ? 'selected' : '' }}>{{ __('msg.unused') }}
                            </option>
                        </select>
                    </form>
                    <form action="{{ route('arcade.chips.store') }}" method="POST" class="confirm-submit">
                        {{-- 修正路由名稱 --}}
                        @csrf
                        <x-button>
                            {{ __('msg.add_token') }}
                        </x-button>
                    </form>
                </div>
            </div>

            <div class="container mx-auto pb-3">
                <div class="bg-white rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div
                        class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg">
                        <div class="w-[4%] break-words items-center text-center">ID</div>
                        <div class="w-[33%] break-words items-center text-center">{{ __('msg.token') }}</div>
                        <div class="w-[20%] break-words items-center text-center">{{ __('msg.expires_at') }}</div>
                        <div class="w-[13%] break-words items-center text-center">{{ __('msg.status') }}</div>
                        <div class="w-[10%] break-words items-center text-center">{{ __('msg.creator') }}</div>
                        <div class="w-[11%] px-1 text-center">{{ __('msg.actions') }}</div>
                        <button type="button" id="printButton"
                            class="w-[9%] qrcodeprint px-1 text-center text-xs items-center rounded-lg p-2 bg-yellow-200 hover:bg-yellow-300"
                            onclick="handlePrintClick()">
                            列印
                        </button>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                    @foreach ($chipKeys as $chipKey)
                        <div
                            class="flex items-center my-4 mx-0 border-b border-gray-200 text-sm font-medium text-gray-700">
                            <div class="w-[4%] px-1">{{ $loop->iteration }}</div>
                            <div class="w-[28%] px-1 break-words cursor-copy" id="chipKey_{{ $chipKey->id }}"
                                {{-- Add unique ID --}}
                                onclick="copyToClipboard('{{ $chipKey->key }}', 'chipKey_{{ $chipKey->id }}')">
                                {{-- Use new function --}}
                                {{ $chipKey->key }}
                            </div>
                            <div class="w-[20%]">{{ \Carbon\Carbon::parse($chipKey->expires_at)->format('ymd/Hi') }}</div>
                            <div class="w-[13%] px-1 text-center">
                                @if ($chipKey->status == 'used')
                                    <span class="text-red-500">{{ __('msg.used') }}</span>
                                @else
                                    <span class="text-green-500">{{ __('msg.unused') }}</span>
                                @endif
                            </div>
                            <div class="w-[10%] px-1 text-center">{{ $chipKey->creator->name ?? 'Unknown' }}</div>
                            <div class="w-[11%] px-1 text-center">
                                <form action="{{ route('arcade.chips.destroy', $chipKey->id) }}" method="POST"
                                    {{-- 修正路由名稱 --}} class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-end px-2 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                                        onclick="return confirm('{{ __('msg.confirm_delete') }}');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <div class="w-[9%] break-words text-center">
                                <input type="checkbox" name="selected_ids[]" value="{{ $chipKey->id }}"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $title = __('msg.chip_token');
@endphp
