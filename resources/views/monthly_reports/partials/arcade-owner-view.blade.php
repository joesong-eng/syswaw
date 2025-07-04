{{-- Arcade Owner View: 顯示本店的總覽，以及需要支付給各機主的分成明細 --}}

@php
    $arcadeDetail = $details->first();
@endphp

@if ($arcadeDetail)
    <!-- 本店總覽 -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">本店財務總覽 -
                {{ $arcadeDetail->reportable->name ?? '' }}</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">全店總淨利</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                        {{ number_format($arcadeDetail->net_profit, 2) }}</p>
                </div>
                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">應付機主總額</p>
                    @php
                        $totalPayoutToOwners = $breakdown->flatten()->sum('machine_owner_share');
                    @endphp
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">-
                        {{ number_format($totalPayoutToOwners, 2) }}</p>
                </div>
                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">應付平台抽成</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">-
                        {{ number_format($arcadeDetail->platform_share_amount, 2) }}</p>
                </div>
                <div class="p-4 bg-green-100 dark:bg-green-800/50 rounded-lg">
                    <p class="text-sm text-green-800 dark:text-green-300">本店實收</p>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-200">
                        {{ number_format($arcadeDetail->revenue_share_amount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 分潤明細 (按機主分組) -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">應付廠商分潤明細</h4>
            <div class="mt-4 space-y-6">
                @forelse($breakdown as $ownerName => $machineReports)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <h5 class="font-semibold text-indigo-700 dark:text-indigo-300">{{ $ownerName }}</h5>
                        <table class="mt-2 min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-2 py-1 text-left">機台</th>
                                    <th class="px-2 py-1 text-right">該機淨利</th>
                                    <th class="px-2 py-1 text-right">應付分成</th>
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
                                            class="px-2 py-1 text-sm text-right font-medium text-red-600 dark:text-red-400">
                                            - {{ number_format($report['machine_owner_share'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-gray-300 dark:border-gray-600">
                                <tr>
                                    <td class="px-2 py-1 font-bold text-sm text-gray-900 dark:text-gray-100">小計</td>
                                    <td class="px-2 py-1 font-bold text-sm text-right text-gray-900 dark:text-gray-100">
                                        {{ number_format($machineReports->sum('net_profit'), 2) }}</td>
                                    <td class="px-2 py-1 font-bold text-sm text-right text-red-600 dark:text-red-400">-
                                        {{ number_format($machineReports->sum('machine_owner_share'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400">本店無需要支付的機主分潤。</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    <p class="text-center text-gray-500 dark:text-gray-400">找不到您的場地報表數據。</p>
@endif
