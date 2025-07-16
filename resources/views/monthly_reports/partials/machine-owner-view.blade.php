{{-- Machine Owner View: 顯示總應收款，以及各場地的收入明細 --}}

@php
    $ownerDetail = $details->first();
@endphp

@if ($ownerDetail)
    <!-- 總覽 -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white text-center">
            <p class="text-sm text-gray-500">本月應收總額</p>
            <p class="text-4xl font-extrabold text-blue-600 mt-1">
                {{ number_format($ownerDetail->revenue_share_amount, 2) }}</p>
            <p class="text-xs text-gray-400 mt-2">(總淨利:
                {{ number_format($ownerDetail->net_profit, 2) }} | 平台抽成:
                {{ number_format($ownerDetail->platform_share_amount, 2) }})</p>
        </div>
    </div>

    <!-- 收入來源明細 (按場地分組) -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-t border-gray-200">
            <h4 class="text-md font-semibold text-gray-800">收入來源明細</h4>
            <div class="mt-4 space-y-6">
                @forelse($breakdown as $arcadeName => $machineReports)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h5 class="font-semibold text-indigo-700">{{ $arcadeName }}</h5>
                        <table class="mt-2 min-w-full">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="px-2 py-1 text-left">機台</th>
                                    <th class="px-2 py-1 text-right">該機淨利</th>
                                    <th class="px-2 py-1 text-right">我的分成</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($machineReports as $report)
                                    <tr>
                                        <td class="px-2 py-1 text-sm text-gray-800">
                                            {{ $report['machine_name'] }}</td>
                                        <td class="px-2 py-1 text-sm text-right text-gray-600">
                                            {{ number_format($report['net_profit'], 2) }}</td>
                                        <td
                                            class="px-2 py-1 text-sm text-right font-medium text-green-600">
                                            + {{ number_format($report['machine_owner_share'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-gray-300">
                                <tr>
                                    <td class="px-2 py-1 font-bold text-sm text-gray-900">小計</td>
                                    <td class="px-2 py-1 font-bold text-sm text-right text-gray-900">
                                        {{ number_format($machineReports->sum('net_profit'), 2) }}</td>
                                    <td
                                        class="px-2 py-1 font-bold text-sm text-right text-green-600">
                                        + {{ number_format($machineReports->sum('machine_owner_share'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @empty
                    <p class="text-center text-gray-500">沒有找到收入來源明細。</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    <p class="text-center text-gray-500">找不到您的機台分潤報表數據。</p>
@endif
