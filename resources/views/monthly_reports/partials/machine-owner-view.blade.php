{{-- Machine Owner View: 顯示總應收款，以及各場地的收入明細 --}}

@php
    $ownerDetail = $details->first();
@endphp

@if ($ownerDetail)
    <!-- 總覽 -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">本月應收總額</p>
            <p class="text-4xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">
                {{ number_format($ownerDetail->revenue_share_amount, 2) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">(總淨利:
                {{ number_format($ownerDetail->net_profit, 2) }} | 平台抽成:
                {{ number_format($ownerDetail->platform_share_amount, 2) }})</p>
        </div>
    </div>

    <!-- 收入來源明細 (按場地分組) -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">收入來源明細</h4>
            <div class="mt-4 space-y-6">
                @forelse($breakdown as $arcadeName => $machineReports)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <h5 class="font-semibold text-indigo-700 dark:text-indigo-300">{{ $arcadeName }}</h5>
                        <table class="mt-2 min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-2 py-1 text-left">機台</th>
                                    <th class="px-2 py-1 text-right">該機淨利</th>
                                    <th class="px-2 py-1 text-right">我的分成</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($machineReports as $report)
                                    <tr>
                                        <td class="px-2 py-1 text-sm text-gray-800 dark:text-gray-200">
                                            {{ $report['machine_name'] }}</td>
                                        <td class="px-2 py-1 text-sm text-right text-gray-600 dark:text-gray-300">
                                            {{ number_format($report['net_profit'], 2) }}</td>
                                        <td
                                            class="px-2 py-1 text-sm text-right font-medium text-green-600 dark:text-green-400">
                                            + {{ number_format($report['machine_owner_share'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-gray-300 dark:border-gray-600">
                                <tr>
                                    <td class="px-2 py-1 font-bold text-sm text-gray-900 dark:text-gray-100">小計</td>
                                    <td class="px-2 py-1 font-bold text-sm text-right text-gray-900 dark:text-gray-100">
                                        {{ number_format($machineReports->sum('net_profit'), 2) }}</td>
                                    <td
                                        class="px-2 py-1 font-bold text-sm text-right text-green-600 dark:text-green-400">
                                        + {{ number_format($machineReports->sum('machine_owner_share'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400">沒有找到收入來源明細。</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    <p class="text-center text-gray-500 dark:text-gray-400">找不到您的機台分潤報表數據。</p>
@endif
