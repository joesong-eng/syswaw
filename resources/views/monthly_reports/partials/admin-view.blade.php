{{-- Admin View: 顯示所有場主和機主的分潤匯總 --}}

@php
    $arcadeDetails = $details['App\\Models\\Arcade'] ?? collect();
    $machineOwnerDetails = $details['App\\Models\\User'] ?? collect();
@endphp

<!-- 場主分潤匯總 -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <h4 class="text-md font-semibold text-gray-800">場主分潤匯總</h4>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            場地名稱</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                            總淨利</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                            平台抽成</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                            場主實收</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($arcadeDetails as $detail)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $detail->reportable->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600">
                                {{ number_format($detail->net_profit, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600">
                                {{ number_format($detail->platform_share_amount, 2) }}</td>
                            <td
                                class="px-4 py-2 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                {{ number_format($detail->revenue_share_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">沒有場主分潤數據。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 機主分潤匯總 -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <h4 class="text-md font-semibold text-gray-800">機主分潤匯總</h4>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            機主名稱</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                            總淨利</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                            平台抽成</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                            機主應收</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($machineOwnerDetails as $detail)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $detail->reportable->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600">
                                {{ number_format($detail->net_profit, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600">
                                {{ number_format($detail->platform_share_amount, 2) }}</td>
                            <td
                                class="px-4 py-2 whitespace-nowrap text-sm text-right font-bold text-blue-600">
                                {{ number_format($detail->revenue_share_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500">沒有機主分潤數據。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
