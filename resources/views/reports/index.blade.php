{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center">
        <x-svg-icons name="chart" classes="h-6 w-6 mr-2" /> {{-- 假設有一個 chart icon --}}
        {{ __('msg.reports') }}
    </h2>
@endsection

@section('content')
    {{-- 調整外層容器，確保頁面不整體滾動 --}}
    {{-- 新增 h-full 或 min-h-full 以確保內容區塊佔滿可用高度 --}}
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900 py-4 px-2 h-full">
        {{-- max-w-4xl 保持不變，但移除可能會導致溢出的固定高度，讓內容自適應 --}}
        <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 flex flex-col h-full">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ __('msg.filter_reports') }}</h3>

            <form action="{{ route('admin.reports.index') }}" method="GET" class="mb-6"> {{-- *** 路由已修改 *** --}}
                <div class="flex flex-wrap -mx-1 mb-4">
                    {{-- 遊藝場篩選 --}}
                    <div class="px-1 w-1/3  lg:w-1/5  flex-grow mb-4">
                        <label for="arcade_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.arcade') }}
                        </label>
                        <select name="arcade_id" id="arcade_id"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">{{ __('msg.all') }}</option>
                            @foreach ($arcades as $arcade)
                                <option value="{{ $arcade->id }}" {{ (string)$selectedArcade === (string)$arcade->id ? 'selected' : '' }}>
                                    {{ $arcade->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 遊戲機篩選 --}}
                    <div class="px-1 w-1/3  lg:w-1/5  flex-grow mb-4">
                        <label for="machine_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.machine') }}
                        </label>
                        <select name="machine_id" id="machine_id"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">{{ __('msg.all') }}</option>
                            @foreach ($machines as $machine)
                                <option value="{{ $machine->id }}" {{ (string)$selectedMachine === (string)$machine->id ? 'selected' : '' }}>
                                    {{ $machine->name }} ({{ $machine->machine_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 遊戲機廠商 (Owner) --}}
                    <div class="px-1 w-1/3  lg:w-1/5  flex-grow mb-4">
                        <label for="owner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.owner') }}
                        </label>
                        <select name="owner_id" id="owner_id"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">{{ __('msg.all') }}</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" {{ (string)$selectedOwner === (string)$owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 機器類型篩選 --}}
                    <div class="px-1 w-2/5 lg:w-1/5 flex-grow mb-4">
                        <label for="machine_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.machine_type') }}
                        </label>
                        <select name="machine_type" id="machine_type"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">{{ __('msg.all') }}</option>
                            @foreach ($machineTypes as $type)
                                <option value="{{ $type }}" {{ $selectedMachineType === $type ? 'selected' : '' }}>
                                    {{ __('msg.' . $type) }} {{-- 假設有翻譯 key --}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 時間區間篩選 --}}
                    <div class="px-1 w-2/5  lg:w-1/5 flex-grow mb-4">
                        <label for="time_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('msg.time_range') }}
                        </label>
                        <select name="time_range" id="time_range"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="today" {{ $selectedTimeRange === 'today' ? 'selected' : '' }}>{{ __('msg.today') }}</option>
                            <option value="yesterday" {{ $selectedTimeRange === 'yesterday' ? 'selected' : '' }}>{{ __('msg.yesterday') }}</option>
                            <option value="last_3_days" {{ $selectedTimeRange === 'last_3_days' ? 'selected' : '' }}>{{ __('msg.last_3_days') }}</option>
                            <option value="this_week" {{ $selectedTimeRange === 'this_week' ? 'selected' : '' }}>{{ __('msg.this_week') }}</option>
                            <option value="last_week" {{ $selectedTimeRange === 'last_week' ? 'selected' : '' }}>{{ __('msg.last_week') }}</option>
                            <option value="last_3_weeks" {{ $selectedTimeRange === 'last_3_weeks' ? 'selected' : '' }}>{{ __('msg.last_3_weeks') }}</option>
                            <option value="this_month" {{ $selectedTimeRange === 'this_month' ? 'selected' : '' }}>{{ __('msg.this_month') }}</option>
                            <option value="last_month" {{ $selectedTimeRange === 'last_month' ? 'selected' : '' }}>{{ __('msg.last_month') }}</option>
                            <option value="last_3_months" {{ $selectedTimeRange === 'last_3_months' ? 'selected' : '' }}>{{ __('msg.last_3_months') }}</option>
                        </select>
                    </div>

                    <div class="flex w-1/5 lg:w-full justify-end">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md shadow-sm">
                            {{ __('msg.apply_filters') }}
                        </button>
                    </div>
                </div>

            </form>

            {{-- 報告顯示區塊 --}}
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ __('msg.report_results') }}</h3>

            @if (count($reports) > 0)
                {{-- 讓這個父元素可滾動 --}}
                <div class="flex-grow overflow-y-auto rounded-lg shadow"> {{-- *** 新增 flex-grow 和 overflow-y-auto *** --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10"> {{-- *** sticky top-0 z-10 讓表頭固定 *** --}}
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('msg.arcade') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('msg.machine') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('msg.machine_type') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('msg.start_time') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('msg.end_time') }}
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('msg.revenue') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $report['arcade_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $report['machine_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ __('msg.' . $report['machine_type']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $report['start_time'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $report['end_time'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        @if (isset($report['message']))
                                            <span class="text-red-500">{{ $report['message'] }}</span>
                                        @else
                                            ${{ number_format($report['revenue'], 2) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600 dark:text-gray-400 mt-4">{{ __('msg.no_reports_found') }}</p>
            @endif
        </div>
    </div>
@endsection