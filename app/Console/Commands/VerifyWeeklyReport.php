<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Machine;
use App\Models\MachineData;
use App\Models\User; // 如果需要獲取 owner 資訊，雖然這裡主要關注機台數據
use App\Models\Arcade; // 如果需要獲取 arcade 資訊
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VerifyWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:verify-last-week
                            {--machine_id= : Specify a single machine ID to verify}
                            {--arcade_id= : Specify an arcade ID to filter machines}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifies report calculations for last week, optionally for a specific machine or arcade.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * 增加本週的查詢測試
     * @return int
     */
    public function handle()
    {
        $this->info('Starting weekly report verification...');

        // // 1. 定義上週的日期範圍
        // $startDate = Carbon::now()->subWeek()->startOfWeek(); // 上週的開始日期
        // $endDate = Carbon::now()->subWeek()->endOfWeek();     // 上週的結束日期

        // 1. 定義本週的日期範圍
        $startDate = Carbon::now()->startOfWeek(); // 本週的開始日期
        $endDate = Carbon::now()->endOfWeek();     // 本週的結束日期

        $this->info("Verifying report for period: {$startDate->toDateString()} to {$endDate->toDateString()}");

        // 2. 構建機台查詢 (簡化版，這裡不考慮用戶權限，因為是後台驗證)
        $machineQuery = Machine::query();

        // 應用命令列選項的篩選
        if ($this->option('machine_id')) {
            $machineQuery->where('id', $this->option('machine_id'));
            $this->info("Filtering by machine ID: {$this->option('machine_id')}");
        }
        if ($this->option('arcade_id')) {
            $machineQuery->where('arcade_id', $this->option('arcade_id'));
            $this->info("Filtering by arcade ID: {$this->option('arcade_id')}");
        }

        $machines = $machineQuery->with(['arcade:id,name', 'owner:id,name'])->get();
        $machineIds = $machines->pluck('id');

        if ($machineIds->isEmpty()) {
            $this->error('No machines found for the specified criteria. Exiting.');
            return Command::FAILURE;
        }

        $this->info("Found " . $machines->count() . " machines for verification.");

        // 3. 獲取期末值：在 endDate 或之前的最後一筆記錄
        $latestRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(timestamp) as max_timestamp'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<=', $endDate->endOfDay())
            ->groupBy('machine_id');

        $latestRecords = MachineData::joinSub($latestRecordsSubquery, 'latest', function ($join) {
            $join->on('machine_data.timestamp', '=', 'latest.max_timestamp');
        })
            ->whereIn('machine_data.machine_id', $machineIds) // 再次過濾確保只包含我們需要的機台
            ->get()
            ->keyBy('machine_id');


        // 4. 獲取期初值：在 startDate 之前的最後一筆記錄
        $initialRecordsSubquery = MachineData::select('machine_id', DB::raw('MAX(timestamp) as max_timestamp'))
            ->whereIn('machine_id', $machineIds)
            ->where('timestamp', '<', $startDate->startOfDay())
            ->groupBy('machine_id');

        $initialRecords = MachineData::joinSub($initialRecordsSubquery, 'initial', function ($join) {
            $join->on('machine_data.timestamp', '=', 'initial.max_timestamp');
        })
            ->whereIn('machine_data.machine_id', $machineIds) // 再次過濾確保只包含我們需要的機台
            ->get()
            ->keyBy('machine_id');

        $reportData = [];
        $totalSummary = [ // 用於總計
            'credit_in_value' => 0,
            'assign_credit_value' => 0,
            'total_revenue' => 0,
            'total_cost' => 0,
            'net_profit' => 0,
        ];

        // 5. 計算增量並生成報表數據
        $this->info('Calculating report data...');
        foreach ($machines as $machine) {
            $latest = $latestRecords->get($machine->id);

            // 如果連期末值都找不到（即 endDate 之前沒有任何數據），則跳過此機台
            if (!$latest) {
                $this->warn("Skipping machine '{$machine->name}' (ID: {$machine->id}) - No latest data found up to {$endDate->toDateString()}.");
                continue;
            }

            $initial = $initialRecords->get($machine->id);

            // 如果找不到期初值（即 startDate 之前沒有任何數據），則期初值為 0
            // 確保所有關鍵字段都在 $initialValues 中有預設值
            $initialValues = $initial ? $initial->getAttributes() : array_fill_keys(['credit_in', 'assign_credit', 'coin_out', 'ball_in', 'ball_out', 'bill_denomination'], 0);

            // 計算增量
            $delta_credit_in = $latest->credit_in - $initialValues['credit_in'];
            $delta_assign_credit = $latest->assign_credit - $initialValues['assign_credit'];
            $delta_coin_out = $latest->coin_out - $initialValues['coin_out'];

            // 確保所有值都是數字，避免 null 或非數字導致錯誤
            $coinInputValue = (float)($machine->coin_input_value ?? 0);
            $creditButtonValue = (float)($machine->credit_button_value ?? 0);
            $payoutUnitValue = (float)($machine->payout_unit_value ?? 0);


            // 計算營收
            $revenue_from_coins = $delta_credit_in * $coinInputValue;
            $revenue_from_assign = $delta_assign_credit * $creditButtonValue;
            $total_revenue = $revenue_from_coins + $revenue_from_assign;

            // 計算成本
            $total_cost = $delta_coin_out * $payoutUnitValue;

            // 計算淨利
            $net_profit = $total_revenue - $total_cost;

            $reportData[] = [
                'machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'arcade_name' => $machine->arcade->name ?? 'N/A',
                'owner_name' => $machine->owner->name ?? 'N/A',
                'credit_in_initial' => $initialValues['credit_in'],
                'credit_in_latest' => $latest->credit_in,
                'credit_in_delta' => $delta_credit_in,
                'assign_credit_initial' => $initialValues['assign_credit'],
                'assign_credit_latest' => $latest->assign_credit,
                'assign_credit_delta' => $delta_assign_credit,
                'coin_out_initial' => $initialValues['coin_out'],
                'coin_out_latest' => $latest->coin_out,
                'coin_out_delta' => $delta_coin_out,
                'credit_in_value' => $revenue_from_coins,
                'assign_credit_value' => $revenue_from_assign,
                'total_revenue' => $total_revenue,
                'total_cost' => $total_cost,
                'net_profit' => $net_profit,
            ];

            // 累加到總計
            $totalSummary['credit_in_value'] += $revenue_from_coins;
            $totalSummary['assign_credit_value'] += $revenue_from_assign;
            $totalSummary['total_revenue'] += $total_revenue;
            $totalSummary['total_cost'] += $total_cost;
            $totalSummary['net_profit'] += $net_profit;
        }

        // 6. 顯示結果
        if (empty($reportData)) {
            $this->warn('No report data generated for the specified period and filters.');
        } else {
            // 定義要顯示的表格標頭
            $headers = [
                '機台ID',
                '機台',
                '場地',
                '廠商',
                '期初投幣',
                '期末投幣',
                '投幣增量',
                '投幣收入',
                '期初開分',
                '期末開分',
                '開分增量',
                '開分收入',
                '期初支出',
                '期末支出',
                '支出增量',
                '總支出',
                '淨利'
            ];

            // 格式化數據以便顯示
            $formattedData = collect($reportData)->map(function ($item) {
                return [
                    $item['machine_id'],
                    $item['machine_name'],
                    $item['arcade_name'],
                    $item['owner_name'],
                    round($item['credit_in_initial'], 2),
                    round($item['credit_in_latest'], 2),
                    round($item['credit_in_delta'], 2),
                    round($item['credit_in_value'], 2),
                    round($item['assign_credit_initial'], 2),
                    round($item['assign_credit_latest'], 2),
                    round($item['assign_credit_delta'], 2),
                    round($item['assign_credit_value'], 2),
                    round($item['coin_out_initial'], 2),
                    round($item['coin_out_latest'], 2),
                    round($item['coin_out_delta'], 2),
                    round($item['total_cost'], 2),
                    round($item['net_profit'], 2),
                ];
            })->all();

            $this->table($headers, $formattedData);

            $this->info("\n--- 總計 (Total Summary) ---");
            $this->info("投幣收入總計: " . round($totalSummary['credit_in_value'], 2));
            $this->info("開分收入總計: " . round($totalSummary['assign_credit_value'], 2));
            $this->info("總收入: " . round($totalSummary['total_revenue'], 2));
            $this->info("總支出: " . round($totalSummary['total_cost'], 2));
            $this->info("總淨利: " . round($totalSummary['net_profit'], 2));
        }

        $this->info('Weekly report verification completed.');

        return Command::SUCCESS;
    }
}
