@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        月結報表列表
    </h2>
@endsection

@section('content')
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-scroll shadow-xl sm:rounded-lg">
                <div class="p-2">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="text-gray-800">
                            <tr>
                                <th class="px-1 py-3 text-center text-xs font-medium text-gray-500 uppercase">報表單號</th>
                                <th class="px-1 py-3 text-center text-xs font-medium text-gray-500 uppercase">報表區間</th>
                                <th class="px-1 py-3 text-right text-xs font-medium text-gray-500 uppercase">全平台淨利</th>
                                <th class="px-1 py-3 text-right text-xs font-medium text-gray-500 uppercase">平台收益</th>
                                <th class="px-1 py-3 text-center text-xs font-medium text-gray-500 uppercase">生成時間</th>
                                <th class="px-1 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                            </tr>
                        </thead>
                        <tbody class=" divide-y divide-gray-200 text-dark">
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="px-1 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 text-center">
                                        <a href="{{ route('admin.monthly-reports.show', $report) }}"
                                            class="hover:underline">
                                            {{ $report->report_number }}
                                        </a>
                                    </td>
                                    <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{-- {{ $report->period_start }} ~ {{ $report->period_end }} --}}
                                        {{ $report->period_start->format('y-md') }} ~
                                        {{ $report->period_end->format('y-md') }}</td>
                                    <td class="px-1 py-4 whitespace-nowrap text-sm text-right">
                                        {{ number_format($report->total_net_profit, 2) }}</td>
                                    <td class="px-1 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">
                                        {{ number_format($report->platform_profit, 2) }}</td>
                                    <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{ $report->generated_at->format('y-md h:m') }}</td>
                                    <td class="px-1 py-4 whitespace-nowrap text-sm text-right">
                                        <a href="{{ route('admin.monthly-reports.show', $report) }}"
                                            class="text-indigo-600 hover:text-indigo-900">查看</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
