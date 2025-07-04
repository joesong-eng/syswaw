{{-- /www/wwwroot/syswaw/resources/views/arcade/statistics/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.arcade_statistics') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">
                        {{ __('msg.statistics_summary') }} @if (isset($arcade))
                            - {{ $arcade->name }}
                        @endif
                    </h3>

                    @if (isset($ballInTotal) && isset($ballOutTotal))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Ball In Card --}}
                            <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                                <h4 class="text-md font-semibold text-gray-700">
                                    {{ __('msg.ball_in_total') }}</h4>
                                <p class="text-3xl font-bold text-blue-600 mt-2">
                                    {{ number_format($ballInTotal) }}</p>
                            </div>

                            {{-- Ball Out Card --}}
                            <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                                <h4 class="text-md font-semibold text-gray-700">
                                    {{ __('msg.ball_out_total') }}</h4>
                                <p class="text-3xl font-bold text-green-600 mt-2">
                                    {{ number_format($ballOutTotal) }}</p>
                            </div>

                            {{-- Net Ball Card --}}
                            <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                                <h4 class="text-md font-semibold text-gray-700">
                                    {{ __('msg.net_ball_count') }}</h4>
                                <p
                                    class="text-3xl font-bold {{ $ballInTotal - $ballOutTotal >= 0 ? 'text-indigo-600' : 'text-red-600' }} mt-2">
                                    {{ number_format($ballInTotal - $ballOutTotal) }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-600">{{ __('msg.no_statistics_data') }}</p>
                    @endif

                    {{-- 您可以在此處添加更多詳細的統計數據或圖表 --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    // $title = __('msg.arcade_statistics'); // 用於設定瀏覽器分頁標題 (如果您的 layouts.app 有支援)
@endphp
