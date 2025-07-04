{{-- Admin View: 顯示所有場主和機主的分潤匯總 --}}

@php
    $arcadeDetails = $details['App\\Models\\Arcade'] ?? collect();
    $machineOwnerDetails = $details['App\\Models\\User'] ?? collect();
@endphp

<!-- 場主分潤匯總 -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">場主分潤匯總</h4>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            場地名稱</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            總淨利</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            平台抽成</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            場主實收</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($arcadeDetails as $detail)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $detail->reportable->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                {{ number_format($detail->net_profit, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                {{ number_format($detail->platform_share_amount, 2) }}</td>
                            <td
                                class="px-4 py-2 whitespace-nowrap text-sm text-right font-bold text-green-600 dark:text-green-400">
                                {{ number_format($detail->revenue_share_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">沒有場主分潤數據。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 機主分潤匯總 -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">機主分潤匯總</h4>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            機主名稱</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            總淨利</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            平台抽成</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            機主應收</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($machineOwnerDetails as $detail)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $detail->reportable->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                {{ number_format($detail->net_profit, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                {{ number_format($detail->platform_share_amount, 2) }}</td>
                            <td
                                class="px-4 py-2 whitespace-nowrap text-sm text-right font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($detail->revenue_share_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">沒有機主分潤數據。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
