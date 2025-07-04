{{-- Route::get('storesKey', [StoreController::class, 'storesKey'])->name('storesKey'); --}}
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.token_management') }}
    </h2>
@endsection
@section('content')
    <div class="flex justify-center bg-gray-100">
        <div class="relative w-full bg-white bg-opacity-60 shadow-lg rounded-lg">
            <div class="flex justify-end items-center mb-2 pt-2">
                <form action="{{ route('admin.arcadeKey.store') }}" method="POST">
                    @csrf
                    <x-button type="submit">
                        {{ __('msg.create') }} {{ __('msg.token') }}</x-button>
                </form>
            </div>

            <div class="container mx-auto pb-3">
                <div class="bg-white rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg">
                        <div class="w-[4%]  px-1 border-r">#</div>
                        <div class="w-[35%] break-words">金鑰</div>
                        <div class="w-[15%] text-center">{{ __('msg.used') }}</div>
                        <div class="w-[16%] text-center">{{ __('msg.creator') }}</div>
                        <div class="w-[16%] text-center border-l">{{ __('msg.expires_at') }}</div>
                        <div class="w-[9%]  text-center ">{{ __('msg.actions') }}</div>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                    @foreach ($arcadeKeys as $arcadeKey)
                        <div class="flex items-center my-4 mx-0 border-b border-gray-200 text-sm font-medium text-gray-700">
                            <div class="w-[4%] px-1">{{ $loop->iteration }}</div>
                            @if ($arcadeKey->used)
                                <div class="w-[35%] px-1 break-words cursor-default truncate" id="arcadeKey"
                                    title="{{ $arcadeKey->token }}">
                                @else
                                    {{--  分成arcade和chip兩種 --}}
                                    <div class="w-[35%] px-1 break-words cursor-copy truncate" id="arcadeKey"
                                        onclick="copyToClipboardAndGenerateLink('{{ $arcadeKey->token }}','arcades')">
                            @endif{{ $arcadeKey->token }}
                        </div>
                        <div class="w-[15%] px-1 text-center">
                            @if ($arcadeKey->used)
                                <span class="text-bule-500">
                                    <div class="text-xs items-start">
                                        <!-- 顯示綁定的 Arcade 名稱 -->
                                        <div class="font-thin"
                                            style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            @if ($arcadeKey->arcade)
                                                已綁定: {{ $arcadeKey->arcade->name }}
                                            @else
                                                未綁定任何遊藝場
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-red-500" style="white-space: nowrap; ellipsis;">已使用</div>
                                </span>
                            @else
                                <span class="text-green-500">未使用</span>
                            @endif
                        </div>


                        <div class="w-[16%] px-1 items-center  text-center break-words">
                            {{ $arcadeKey->creator->name ?? 'Unknown' }}</div>
                        <div class="w-[16%] items-center  text-center font-thin text-xs break-words">
                            {{ \Carbon\Carbon::parse($arcadeKey->expires_at)->format('ymd/Hi') }}</div>
                        <div class="w-[9%] break-words text-center m-auto space-x-1 border-l">
                            @if (!$arcadeKey->arcade)
                                <form action="{{ route('admin.arcadeKey.destroy', $arcadeKey) }}" method="POST"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-end px-2 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                                        onclick="return confirm('{{ __('msg.confirm_delete') }}');">
                                        <x-icon.trash class='h-6 w-6 text-gray-500' />
                                    </button>
                                </form>
                            @else
                                <!-- 按钮 -->
                                <button type="button"
                                    class="text-end px-2 py-2 bg-gray-500 text-white rounded-md transition cursor-default"
                                    onclick="showLocalMessage()">
                                    <x-icon.trash class='h-6 w-6 text-gray-500' />
                                </button>
                            @endif
                        </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    </div>
@endsection
@php
    $title = __('msg.arcade_key_management');
@endphp
