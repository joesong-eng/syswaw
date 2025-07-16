{{-- /www/wwwroot/syswaw/resources/views/admin/reports/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.reports') }}_admin
    </h2>
@endsection

@section('content')
    <div x-data="reportPage()" x-init="init(
        {{ json_encode($arcades ?? []) }},
        {{ json_encode($machineTypes ?? []) }},
        {{ json_encode($machinesForFilter ?? []) }},
        {{ json_encode($owners ?? []) }},
        {{ json_encode(session('reportData') ?? []) }},
        {{ json_encode(session('filters') ?? []) }}
    )" class="py-1">
        <div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
            <!-- 篩選器區域 (可折疊) -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-2 absolute top-0 right-0 z-10">
                <div class="p-1 px-3 sm:px-6 bg-white border-b border-gray-200">
                    <!-- 篩選條件標題列 -->
                    <div class="flex justify-between items-center cursor-pointer bg-gray-100 hover:bg-gray-200 transition-colors px-3 py-2 rounded-md"
                        @click="filtersOpen = !filtersOpen">
                        <div class="flex items-center text-gray-700 font-semibold text-base">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 4a1 1 0 011-1h12a1 1 0 01.8 1.6L12 11.5V16a1 1 0 01-1.447.894l-2-1A1 1 0 018 15v-3.5L3.2 4.6A1 1 0 013 4z" />
                            </svg>
                            篩選條件
                        </div>
                        <button class="text-gray-500 hover:text-gray-700 transform transition-transform duration-200"
                            :class="{ 'rotate-180': filtersOpen }">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- 篩選表單 -->
                    <div x-cloak x-show="!filtersOpen" x-transition>
                        <form action="{{ route('admin.reports.generate') }}" method="POST" class="mt-2">
                            @csrf
                            <div class="grid grid-cols-1  lg:grid-cols-3 xl:grid-cols-5 gap-4 items-end">
                                <!-- 日期區間 -->
                                <div>
                                    <label for="period"
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.period') }}</label>
                                    <select name="period" id="period"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="today" {{ old('period', 'today') == 'today' ? 'selected' : '' }}>今天
                                        </option>
                                        <option value="yesterday" {{ old('period') == 'yesterday' ? 'selected' : '' }}>昨天
                                        </option>
                                        <option value="last_3_days" {{ old('period') == 'last_3_days' ? 'selected' : '' }}>
                                            最近3天</option>
                                        <option value="this_week" {{ old('period') == 'this_week' ? 'selected' : '' }}>本週
                                        </option>
                                        <option value="last_week" {{ old('period') == 'last_week' ? 'selected' : '' }}>上週
                                        </option>
                                        <option value="this_month" {{ old('period') == 'this_month' ? 'selected' : '' }}>本月
                                        </option>
                                        <option value="last_month" {{ old('period') == 'last_month' ? 'selected' : '' }}>上月
                                        </option>
                                    </select>
                                </div>

                                <!-- 場地 -->
                                <div>
                                    <label for="arcade_id"
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.arcade') }}</label>
                                    <select name="arcade_id" id="arcade_id" x-model="filters.arcade_id"
                                        @change="onFilterChange()"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">所有場地</option>
                                        @foreach ($arcades as $arcade)
                                            <option value="{{ $arcade->id }}"
                                                {{ old('arcade_id') == $arcade->id ? 'selected' : '' }}>
                                                {{ $arcade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 類型 -->
                                <div>
                                    <label for="machine_type"
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.machine_type') }}</label>
                                    <select name="machine_type" id="machine_type" x-model="filters.machine_type"
                                        @change="onFilterChange()"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">所有類型</option>
                                        @foreach ($machineTypes as $key => $name)
                                            <option value="{{ $key }}"
                                                {{ old('machine_type') == $key ? 'selected' : '' }}>{{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 機器 -->
                                <div>
                                    <label for="machine_id"
                                        class="block text-sm font-medium text-gray-700">{{ __('msg.machine') }}</label>
                                    <select name="machine_id" id="machine_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">所有機器</option>
                                        <template x-for="machine in filteredMachines" :key="machine.id">
                                            <option :value="machine.id"
                                                x-text="machine.name + ' (' + (machine.arcade ? machine.arcade.name : '') + ')'"
                                                :selected="machine.id == '{{ old('machine_id') }}'"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- 廠商 -->
                                @if (Auth::user()->hasRole('admin') || $owners->isNotEmpty())
                                    <div>
                                        <label for="owner_id"
                                            class="block text-sm font-medium text-gray-700">{{ __('msg.owner') }}</label>
                                        <select name="owner_id" id="owner_id" x-model="filters.owner_id"
                                            @change="onFilterChange()"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">所有廠商</option>
                                            @foreach ($owners as $owner)
                                                <option value="{{ $owner->id }}"
                                                    {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                                    {{ $owner->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- 行動裝置的產生按鈕 -->
                                <div class="mt-3 flex justify-end">
                                    <button type="submit"
                                        class="items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 block xl:hidden">
                                        產生報表
                                    </button>
                                </div>
                            </div>

                            <!-- 桌機版的產生按鈕 -->
                            <div class="mt-3 flex justify-end">
                                <button type="submit"
                                    class="items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 hidden xl:block">
                                    產生報表
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <!-- 報表結果顯示區 -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-2 sm:px-6 bg-white border-b border-gray-200">
                    <!-- <<< 修改：將標題和新的 checkbox 包在一個 flex 容器中 >>> -->
                    <div class="flex justify-between items-end">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                @if (session('reportTitle'))
                                    {{ session('reportTitle') }}
                                @else
                                    報表結果
                                @endif
                            </h3>
                            @if (session('dateRange'))
                                <div class="mt-1 text-sm text-gray-600 space-y-1">
                                    <p>報表區間：{{ session('dateRange.start') }} ~ {{ session('dateRange.end') }}</p>
                                    @if (session('filterContext.arcade_name'))
                                        <p>場地：<span class="font-semibold">{{ session('filterContext.arcade_name') }}</span>
                                        </p>
                                    @endif
                                    @if (session('filterContext.owner_name'))
                                        <p>廠商：<span class="font-semibold">{{ session('filterContext.owner_name') }}</span>
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- <<< 新增：小數點顯示切換 checkbox >>> -->
                        <div class="flex items-end space-x-1 text-xs">
                            <input type="checkbox" id="showDecimalsCheckbox" x-model="showDecimals"
                                class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="showDecimalsCheckbox" class="text-gray-700">顯示小數</label>
                        </div>
                    </div>

                    @if (session('reportData'))
                        @if (empty(session('reportData')))
                            <div class="mt-4 border-dashed border-2 border-gray-300 rounded-lg p-8 text-center">
                                <p class="text-gray-500">在指定條件下找不到數據</p>
                            </div>
                        @else
                            @php
                                // 預先計算 colspan 的值
                                $summaryColspan = 1; // '機台' 欄位
                                if (empty(session('filters.arcade_id'))) {
                                    $summaryColspan++; // 如果顯示 '場地'
                                }
                                if (empty(session('filters.owner_id'))) {
                                    $summaryColspan++; // 如果顯示 '廠商'
                                }
                            @endphp
                            <div class="mt-2 overflow-x-auto">
                                <div class="overflow-y-auto max-h-[calc(100vh-150px)]">
                                    <table
                                        class="min-w-full divide-y divide-gray-200 w-full table-fixed border-collapse text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-2 py-0 text-left sticky top-0 bg-gray-100 z-1 w-[20%]">
                                                    機台
                                                </th>
                                                <!-- 場地與廠商（合併為一個 th） -->
                                                @if (empty(session('filters.arcade_id')) || empty(session('filters.owner_id')))
                                                    <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[30%] min-w-[100px]"
                                                        colspan="2">
                                                        <div
                                                            class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4 min-w-[100px]">
                                                            @if (empty(session('filters.arcade_id')))
                                                                <div class="flex-1 text-left min-w-[100px]">場地</div>
                                                            @endif
                                                            @if (empty(session('filters.owner_id')))
                                                                <div class="flex-1 text-left min-w-[100px]">廠商</div>
                                                            @endif
                                                        </div>
                                                    </th>
                                                @endif
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    投幣
                                                </th>
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    開分
                                                </th>
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    收入
                                                </th>
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    洗分
                                                </th>
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    退幣
                                                </th>
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    總支出
                                                </th>
                                                <th class="px-2 py-0 text-center sticky top-0 bg-gray-100 z-1 w-[10%]">
                                                    淨利
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="(data, index) in displayedData" :key="index">
                                                <tr>
                                                    <td class="px-1 py-2  text-sm text-gray-900"
                                                        x-text="data.machine_name"></td>
                                                    @if (empty(session('filters.arcade_id')) || empty(session('filters.owner_id')))
                                                        <td class="px-2 py-2  text-sm text-gray-500" colspan="2"
                                                            style="min-width: 100px;">
                                                            <div
                                                                class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4 min-w-[100px]">
                                                                @if (empty(session('filters.arcade_id')))
                                                                    <div class="flex-1 text-left min-w-[100px] inline-block truncate"
                                                                        x-text="data.arcade_name"></div>
                                                                @endif
                                                                @if (empty(session('filters.owner_id')))
                                                                    <div class="flex-1 text-left  min-w-[100px] inline-block truncate"
                                                                        x-text="data.owner_name"></div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    @endif
                                                    <td class="px-1 py-2  text-sm text-center"
                                                        x-text="formatNumber(data.credit_in_value)"></td>
                                                    <td class="px-1 py-2  text-sm text-center"
                                                        x-text="formatNumber(data.assign_credit_value)"></td>
                                                    <td class="px-1 py-2  text-sm text-center text-green-600 font-semibold"
                                                        x-text="formatNumber(data.total_revenue)"></td>
                                                    <td class="px-1 py-2  text-sm text-center"
                                                        x-text="formatNumber(data.coin_out_delta)"></td>
                                                    <td class="px-1 py-2  text-sm text-center"
                                                        x-text="formatNumber(data.settled_credit_delta)"></td>
                                                    <td class="px-1 py-2  text-sm text-center text-red-600"
                                                        x-text="formatNumber(data.total_cost)"></td>
                                                    <td class="px-1 py-2  text-sm text-center font-bold"
                                                        :class="data.net_profit >= 0 ? 'text-blue-600' : 'text-red-700'"
                                                        x-text="formatNumber(data.net_profit)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <!-- 總計列 -->
                                        <tfoot>
                                            <tr>
                                                <td class="px-1 py-2 text-left text-sm font-bold text-gray-700 sticky bottom-0 bg-gray-100 z-10"
                                                    colspan="{{ empty(session('filters.arcade_id')) && empty(session('filters.owner_id')) ? 3 : (empty(session('filters.arcade_id')) || empty(session('filters.owner_id')) ? 2 : 1) }}">
                                                    頁面總計
                                                </td>
                                                <td class="px-1 py-2 text-sm text-right font-bold sticky bottom-0 bg-gray-100 z-10"
                                                    x-text="formatNumber(pageSummary.credit_in_value)"></td>
                                                <td class="px-1 py-2 text-sm text-right font-bold sticky bottom-0 bg-gray-100 z-10"
                                                    x-text="formatNumber(pageSummary.assign_credit_value)"></td>
                                                <td class="px-1 py-2 text-sm text-right font-bold text-green-700 sticky bottom-0 bg-gray-100 z-10"
                                                    x-text="formatNumber(pageSummary.total_revenue)"></td>
                                                <td class="px-1 py-2 text-sm text-right font-bold sticky bottom-0 bg-gray-100 z-10"
                                                    x-text="formatNumber(pageSummary.coin_out_delta)"></td>
                                                <td class="px-1 py-2 text-sm text-right font-bold sticky bottom-0 bg-gray-100 z-10"
                                                    x-text="formatNumber(pageSummary.settled_credit_delta)"></td>
                                                <td class="px-1 py-2 text-sm text-right font-bold text-red-700 sticky bottom-0 bg-gray-100 z-10"
                                                    x-text="formatNumber(pageSummary.total_cost)"></td>
                                                <td class="px-1 py-2 text-sm text-right font-extrabold sticky bottom-0 bg-gray-100 z-10"
                                                    :class="pageSummary.net_profit >= 0 ? 'text-blue-700' : 'text-red-800'"
                                                    x-text="formatNumber(pageSummary.net_profit)"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div x-show="hasMoreData" x-intersect:enter="loadMore()"
                                    class="p-4 text-center text-gray-500">正在載入更多...</div>
                            </div>
                        @endif
                    @else
                        <div class="mt-4 border-dashed border-2 border-gray-300 rounded-lg p-8 text-center">
                            <p class="text-gray-500">請選擇篩選條件並產生報表</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection

@push('style')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* 移除所有 select 元素的默認下拉箭頭（以防未來添加） */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
    </style>
@endpush
@push('scripts')
    <script>
        function reportPage() {
            return {
                allArcades: [],
                allMachineTypes: {},
                allMachines: [],
                allOwners: [],
                reportData: [],
                filtersOpen: true,
                itemsPerPage: 30,
                currentPage: 1,
                showDecimals: false, // 控制是否顯示小數，預設不顯示
                filters: {
                    period: 'today',
                    arcade_id: '',
                    machine_type: '',
                    machine_id: '',
                    owner_id: ''
                },

                init(arcades, machineTypes, machines, owners, sessionReportData, sessionFilters) {
                    this.allArcades = arcades || [];
                    this.allMachineTypes = machineTypes || {};
                    this.allMachines = machines || [];
                    this.allOwners = owners || [];
                    this.reportData = sessionReportData || [];
                    const safeFilters = sessionFilters || {};
                    if (Object.keys(safeFilters).length > 0) this.filters = {
                        ...this.filters,
                        ...safeFilters
                    };
                },
                // <<< 新增：計算總計的 Getter >>>
                get pageSummary() {
                    // 這個 getter 會計算 displayedData (當前頁面可見數據) 的總和
                    const summary = {
                        credit_in_value: 0,
                        assign_credit_value: 0,
                        total_revenue: 0,
                        coin_out_delta: 0,
                        settled_credit_delta: 0,
                        total_cost: 0,
                        net_profit: 0,
                    };

                    // 遍歷當前頁面顯示的數據並累加
                    this.displayedData.forEach(item => {
                        summary.credit_in_value += Number(item.credit_in_value) || 0;
                        summary.assign_credit_value += Number(item.assign_credit_value) || 0;
                        summary.total_revenue += Number(item.total_revenue) || 0;
                        summary.coin_out_delta += Number(item.coin_out_delta) || 0;
                        summary.settled_credit_delta += Number(item.settled_credit_delta) || 0;
                        summary.total_cost += Number(item.total_cost) || 0;
                        summary.net_profit += Number(item.net_profit) || 0;
                    });

                    return summary;
                },
                get filteredMachines() {
                    if (!this.allMachines) return [];
                    let filtered = this.allMachines;
                    if (this.filters.arcade_id) filtered = filtered.filter(m => m.arcade_id == this.filters.arcade_id);
                    if (this.filters.machine_type) filtered = filtered.filter(m => m.machine_type == this.filters
                        .machine_type);
                    if (this.filters.owner_id) filtered = filtered.filter(m => m.owner_id == this.filters.owner_id);
                    return filtered;
                },
                get displayedData() {
                    return this.reportData.slice(0, this.currentPage * this.itemsPerPage);
                },
                get hasMoreData() {
                    return this.displayedData.length < this.reportData.length;
                },
                // <<< 新增：格式化數字的輔助方法 >>>
                formatNumber(value) {
                    const numberValue = Number(value) || 0;
                    if (this.showDecimals) {
                        // 如果勾選了顯示小數，則顯示兩位小數
                        return numberValue.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                    // 否則，四捨五入到整數
                    return Math.round(numberValue).toLocaleString('en-US');
                },
                onFilterChange() {
                    this.filters.machine_id = '';
                },
                loadMore() {
                    if (this.hasMoreData) {
                        this.currentPage++;
                    }
                }

            }
        }
    </script>
@endpush
