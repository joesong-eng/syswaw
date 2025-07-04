<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Machine;
use App\Models\MachineData;
use App\Models\Arcade;
use App\Models\User;
use App\Models\MonthlyReport;
use App\Models\MonthlyReportDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class GenerateMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:generate-monthly
                            {--month= : (可選) 指定要生成的月份，格式 Y-m，例如 "2025-05"}';

    /**
     * The console command description.
     */
    protected $description = '生成上個月的月結總報表和分潤明細報表';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. 確定要生成報表的月份
        $targetMonth = $this->option('month')
            ? Carbon::parse($this->option('month'))->startOfMonth()
            : Carbon::now()->subMonth()->startOfMonth();

        $periodStart = $targetMonth->copy()->startOfMonth();
        $periodEnd = $targetMonth->copy()->endOfMonth();
        $reportNumber = 'MR-' . $targetMonth->format('Ym');

        // 檢查報表是否已存在
        if (MonthlyReport::where('report_number', $reportNumber)->exists()) {
            $this->error("月份 {$targetMonth->format('Y-m')} 的報表已存在 ({$reportNumber})，操作已取消。");
            return 1;
        }

        $this->info("準備生成 {$targetMonth->format('Y-m')} 的月結報表 (區間: {$periodStart->toDateString()} ~ {$periodEnd->toDateString()})...");

        DB::beginTransaction();
        try {
            // 2. 獲取所有活動機台的營運數據
            $machineReports = $this->calculateMachineReports($periodStart, $periodEnd);

            if (empty($machineReports)) {
                $this->warn("在指定月份內沒有找到任何機台的活動數據。");
                DB::rollBack();
                return 0;
            }

            // 3. 匯總總報表數據
            $totalRevenue = collect($machineReports)->sum('total_revenue');
            $totalCost = collect($machineReports)->sum('total_cost');
            $totalNetProfit = collect($machineReports)->sum('net_profit');

            // 4. 計算並匯總各方分潤
            $platformTotalShare = 0;
            $arcadeShares = [];
            $machineOwnerShares = [];

            foreach ($machineReports as $machineId => $report) {
                // 機主分潤
                $machineOwnerShare = $report['net_profit'] * ((float)$report['machine']->revenue_split / 100);
                // 平台抽成 (從原始淨利中抽)
                $platformShare = $report['net_profit'] * ((float)$report['machine']->share_pct / 100);
                // 場主分潤 (剩餘部分)
                $arcadeOwnerShare = $report['net_profit'] - $machineOwnerShare - $platformShare;

                // 累加到平台總抽成
                $platformTotalShare += $platformShare;

                // 按場地ID累加場主分潤
                $arcadeId = $report['machine']->arcade_id;
                if (!isset($arcadeShares[$arcadeId])) {
                    $arcadeShares[$arcadeId] = ['revenue' => 0, 'cost' => 0, 'net_profit' => 0, 'share_amount' => 0, 'platform_share' => 0, 'machine_ids' => []];
                }
                $arcadeShares[$arcadeId]['revenue'] += $report['total_revenue'];
                $arcadeShares[$arcadeId]['cost'] += $report['total_cost'];
                $arcadeShares[$arcadeId]['net_profit'] += $report['net_profit'];
                $arcadeShares[$arcadeId]['share_amount'] += $arcadeOwnerShare;
                $arcadeShares[$arcadeId]['platform_share'] += $platformShare;
                $arcadeShares[$arcadeId]['machine_ids'][] = $machineId;

                // 按機主ID累加機主分潤
                $ownerId = $report['machine']->owner_id;
                if (!isset($machineOwnerShares[$ownerId])) {
                    $machineOwnerShares[$ownerId] = ['revenue' => 0, 'cost' => 0, 'net_profit' => 0, 'share_amount' => 0, 'platform_share' => 0, 'machine_ids' => []];
                }
                $machineOwnerShares[$ownerId]['revenue'] += $report['total_revenue'];
                $machineOwnerShares[$ownerId]['cost'] += $report['total_cost'];
                $machineOwnerShares[$ownerId]['net_profit'] += $report['net_profit'];
                $machineOwnerShares[$ownerId]['share_amount'] += $machineOwnerShare;
                $machineOwnerShares[$ownerId]['platform_share'] += $platformShare; // 機主報表也應知道平台抽成
                $machineOwnerShares[$ownerId]['machine_ids'][] = $machineId;
            }

            // 5. 創建總報表紀錄
            $mainReport = MonthlyReport::create([
                'report_number' => $reportNumber,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_net_profit' => $totalNetProfit,
                'platform_share' => $platformTotalShare,
                'platform_profit' => $platformTotalShare, // 目前平台收益就等於抽成
                'generated_at' => now(),
            ]);
            $this->info("總報表 {$reportNumber} 已創建。");

            // 6. 創建場主分潤明細
            foreach ($arcadeShares as $arcadeId => $data) {
                $arcade = Arcade::find($arcadeId);
                if ($arcade) {
                    $mainReport->details()->create([
                        'reportable_id' => $arcadeId,
                        'reportable_type' => Arcade::class,
                        'total_revenue' => $data['revenue'],
                        'total_cost' => $data['cost'],
                        'net_profit' => $data['net_profit'],
                        'revenue_share_amount' => $data['share_amount'],
                        'platform_share_amount' => $data['platform_share'],
                        'source_data' => json_encode(['machine_ids' => $data['machine_ids']]),
                    ]);
                }
            }
            $this->info(count($arcadeShares) . " 筆場主分潤明細已創建。");

            // 7. 創建機主分潤明細
            foreach ($machineOwnerShares as $ownerId => $data) {
                $owner = User::find($ownerId);
                if ($owner) {
                    $mainReport->details()->create([
                        'reportable_id' => $ownerId,
                        'reportable_type' => User::class,
                        'total_revenue' => $data['revenue'],
                        'total_cost' => $data['cost'],
                        'net_profit' => $data['net_profit'],
                        'revenue_share_amount' => $data['share_amount'],
                        'platform_share_amount' => $data['platform_share'],
                        'source_data' => json_encode(['machine_ids' => $data['machine_ids']]),
                    ]);
                }
            }
            $this->info(count($machineOwnerShares) . " 筆機主分潤明細已創建。");

            DB::commit();
            $this->info("月結報表生成完畢！");
        } catch (Exception $e) {
            DB::rollBack();
            $this->error("生成報表時發生錯誤: " . $e->getMessage());
            $this->error("文件: " . $e->getFile() . " 第 " . $e->getLine() . " 行");
            return 1;
        }

        return 0;
    }

    /**
     * 輔助函式：計算指定時間範圍內所有機台的營運數據
     */
    private function calculateMachineReports(Carbon $startDate, Carbon $endDate): array
    {
        $machines = Machine::where('is_active', true)->get();
        $machineIds = $machines->pluck('id');

        if ($machineIds->isEmpty()) {
            return [];
        }

        // =================================================================
        // <<< 使用與 ReportController 完全相同的、經過驗證的查詢邏輯 >>>

        // 獲取期末值：在 endDate 或之前的最後一筆記錄
        $latestRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<=', $endDate->endOfDay())
            ->groupBy('machine_id');

        $latestRecords = MachineData::joinSub($latestRecordsSubquery, 'latest', function ($join) {
            $join->on('machine_data.id', '=', 'latest.max_id');
        })->get()->keyBy('machine_id');

        // 獲取期初值：在 startDate 之前的最後一筆記錄
        $initialRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(id) as max_id'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<', $startDate->startOfDay())
            ->groupBy('machine_id');

        $initialRecords = MachineData::joinSub($initialRecordsSubquery, 'initial', function ($join) {
            $join->on('machine_data.id', '=', 'initial.max_id');
        })->get()->keyBy('machine_id');

        // =================================================================

        $machineReports = [];
        foreach ($machines as $machine) {
            $latest = $latestRecords->get($machine->id);

            // 如果連期末值都找不到，或者期末值的時間戳早於我們時段的開始，
            // 代表這個機台在該時段內沒有任何活動，直接跳過。
            if (!$latest || $latest->timestamp < $startDate->startOfDay()) {
                continue;
            }

            $initial = $initialRecords->get($machine->id);
            $initialValues = $initial ? $initial->getAttributes() : array_fill_keys(['credit_in', 'assign_credit', 'coin_out'], 0);

            $delta_credit_in = $latest->credit_in - $initialValues['credit_in'];
            $delta_assign_credit = $latest->assign_credit - $initialValues['assign_credit'];
            $delta_coin_out = $latest->coin_out - $initialValues['coin_out'];

            $total_revenue = ($delta_credit_in * (float)$machine->coin_input_value) + ($delta_assign_credit * (float)$machine->credit_button_value);
            $total_cost = $delta_coin_out * (float)$machine->payout_unit_value;

            $machineReports[$machine->id] = [
                'machine' => $machine,
                'total_revenue' => $total_revenue,
                'total_cost' => $total_cost,
                'net_profit' => $total_revenue - $total_cost,
            ];
        }
        return $machineReports;
    }
}
