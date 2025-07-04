<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Machine;
use App\Models\MachineData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class FixHistoricalMachineData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'data:fix-historical-machine-data
                            {--rebuild : 重建現有數據點的數值}
                            {--generate : 依時間範圍生成全新的每小時數據點}
                            {--start-date= : (生成模式) 開始日期 Y-m-d}
                            {--end-date= : (生成模式) 結束日期 Y-m-d}
                            {--machine=* : (可選) 指定要處理的一個或多個 machine_id}';

    /**
     * The console command description.
     */
    protected $description = '【V6.0】重建或生成機台歷史數據';

    // --- 模擬參數設定 ---
    protected const ASSIGN_CREDIT_PROBABILITY = 0.02;
    protected const PINBALL_COIN_INPUT_RANGE = [2, 15];
    protected const PINBALL_RTP_RANGE = [0.8, 1.1];
    protected const CLAW_COIN_INPUT_RANGE = [5, 30];
    protected const CLAW_PRIZE_PROBABILITY = 0.1;
    protected const GAMBLING_COIN_INPUT_RANGE = [10, 50];
    protected const GAMBLING_BILL_INPUT_RANGE = [0, 2];
    protected const GAMBLING_RTP = 0.85;
    protected const SIMPLE_COIN_INPUT_RANGE = [10, 60];
    protected const SIMPLE_PAYOUT_RATIO = 0.05;
    protected const INPUT_ONLY_BILL_INPUT_RANGE = [5, 20];
    protected const DENOMINATION_VALUE_TO_CODE = ['100' => 1, '200' => 2, '500' => 5, '1000' => 10, '2000' => 20];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('rebuild')) {
            return $this->rebuildData();
        }

        if ($this->option('generate')) {
            return $this->generateData();
        }

        $this->error('錯誤：請指定一個模式：--rebuild (重建現有數據) 或 --generate (生成新數據)');
        return 1;
    }

    /**
     * 模式一：生成全新的數據點
     */
    private function generateData(): int
    {
        $startDateStr = $this->option('start-date');
        $endDateStr = $this->option('end-date');
        if (!$startDateStr || !$endDateStr) {
            $this->error('生成模式下，必須提供 --start-date 和 --end-date。');
            return 1;
        }

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $machines = $this->getTargetMachines();
        if ($machines->isEmpty()) {
            return 0;
        }

        $this->info("準備為 {$machines->count()} 台機台生成從 {$startDate->toDateString()} 到 {$endDate->toDateString()} 的數據...");

        DB::beginTransaction();
        try {
            foreach ($machines as $machine) {
                $this->line("正在處理機台 #{$machine->id}: {$machine->name}");

                $lastRecordBefore = MachineData::where('machine_id', $machine->id)
                    ->where('timestamp', '<', $startDate)
                    ->orderBy('timestamp', 'desc')->first();

                $lastTotals = $lastRecordBefore ? $lastRecordBefore->getAttributes() : $this->getZeroedCounters();

                // <<< 關鍵修正：在開始循環前，就從初始值中移除 id >>>
                unset($lastTotals['id']);

                $newData = [];
                $totalHours = $startDate->diffInHours($endDate) + 1;
                $progressBar = $this->output->createProgressBar($totalHours);

                for ($time = $startDate->copy(); $time->lessThanOrEqualTo($endDate); $time->addHour()) {
                    $deltas = $this->getDeltasForMachine($machine);

                    foreach ($deltas as $key => $delta) {
                        if (isset($lastTotals[$key])) $lastTotals[$key] += $delta;
                    }

                    // 現在的 $lastTotals 已經不包含 id 了
                    $newData[] = array_merge($lastTotals, [
                        'machine_id' => $machine->id,
                        'arcade_id' => $machine->arcade_id,
                        'auth_key_id' => $machine->auth_key_id,
                        'machine_type' => $machine->machine_type,
                        'timestamp' => $time->toDateTimeString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $progressBar->advance();
                }

                foreach (array_chunk($newData, 500) as $chunk) {
                    MachineData::insert($chunk);
                }

                $progressBar->finish();
                $this->getOutput()->newLine();
            }

            DB::commit();
            $this->info("數據生成成功！");
        } catch (Exception $e) {
            DB::rollBack();
            $this->error("生成過程中發生錯誤: " . $e->getMessage());
            return 1;
        }
        return 0;
    }

    /**
     * 模式二：重建現有的數據點
     */
    private function rebuildData(): int
    {
        $machines = $this->getTargetMachines();
        if ($machines->isEmpty()) {
            return 0;
        }

        $this->info("準備重建 {$machines->count()} 台機台的所有歷史數據...");

        foreach ($machines as $machine) {
            $this->line("正在重建機台 #{$machine->id}: {$machine->name}");

            $records = MachineData::where('machine_id', $machine->id)->orderBy('timestamp', 'asc')->get();
            if ($records->isEmpty()) {
                $this->warn(" -> 機台無數據，已跳過。");
                continue;
            }

            $lastTotals = $this->getZeroedCounters();
            $progressBar = $this->output->createProgressBar($records->count());

            DB::transaction(function () use ($records, $machine, &$lastTotals) {
                foreach ($records as $record) {
                    $deltas = $this->getDeltasForMachine($machine);
                    foreach ($deltas as $key => $delta) {
                        if (isset($lastTotals[$key])) $lastTotals[$key] += $delta;
                    }
                    $record->update($lastTotals);
                }
            });

            $progressBar->finish();
            $this->getOutput()->newLine();
        }
        $this->info("所有機台數據重建完成！");
        return 0;
    }

    /**
     * 抽離出來的單次增量計算邏輯，與模擬器完全一致
     */
    private function getDeltasForMachine(Machine $machine): array
    {
        $deltas = $this->getZeroedCounters();
        $behaviorTemplate = $this->getBehaviorTemplate($machine->machine_type);

        $hasAssignCreditFeature = $machine->credit_button_value > 0;
        $isAssignCreditTriggered = $hasAssignCreditFeature && (rand(1, 10000) / 10000) <= self::ASSIGN_CREDIT_PROBABILITY;
        if ($isAssignCreditTriggered) $deltas['assign_credit'] = 1;

        switch ($behaviorTemplate) {
            case 'pinball_like':
                if ((float)$machine->payout_unit_value > 0) {
                    $exchangeRate = (float)$machine->coin_input_value / (float)$machine->payout_unit_value;
                    $deltas['credit_in'] = rand(...self::PINBALL_COIN_INPUT_RANGE);
                    $balls_to_play = $deltas['credit_in'] * $exchangeRate;
                    if ($isAssignCreditTriggered) $balls_to_play += (float)$machine->credit_button_value / (float)$machine->payout_unit_value;
                    $deltas['ball_in'] = $balls_to_play;
                    $rtp = random_int(self::PINBALL_RTP_RANGE[0] * 100, self::PINBALL_RTP_RANGE[1] * 100) / 100;
                    $won_balls = $balls_to_play * $rtp;
                    $deltas['ball_out'] = $balls_to_play + $won_balls;
                }
                break;
            case 'claw_like':
                $playsFromCoins = rand(...self::CLAW_COIN_INPUT_RANGE);
                $deltas['credit_in'] = $playsFromCoins;
                $playsFromAssign = $isAssignCreditTriggered && (float)$machine->coin_input_value > 0 ? floor((float)$machine->credit_button_value / (float)$machine->coin_input_value) : 0;
                $totalPlays = $playsFromCoins + $playsFromAssign;
                for ($i = 0; $i < $totalPlays; $i++) {
                    if ((rand(1, 100) / 100) <= self::CLAW_PRIZE_PROBABILITY) $deltas['coin_out']++;
                }
                break;
            case 'gambling_like':
                if ((float)$machine->coin_input_value > 0 && (float)$machine->payout_unit_value > 0) {
                    $playsFromCoins = rand(...self::GAMBLING_COIN_INPUT_RANGE);
                    $deltas['credit_in'] = $playsFromCoins;
                    $playsFromAssign = $isAssignCreditTriggered ? floor((float)$machine->credit_button_value / (float)$machine->coin_input_value) : 0;
                    $playsFromBills = 0;
                    if ($machine->bill_acceptor_enabled) {
                        $accepted = json_decode($machine->accepted_denominations, true) ?: [];
                        $billCount = rand(...self::GAMBLING_BILL_INPUT_RANGE);
                        for ($i = 0; $i < $billCount; $i++) {
                            if (!empty($accepted)) {
                                $billValueStr = (string) $accepted[array_rand($accepted)];
                                if (isset(self::DENOMINATION_VALUE_TO_CODE[$billValueStr])) $deltas['bill_denomination'] += self::DENOMINATION_VALUE_TO_CODE[$billValueStr];
                                $playsFromBills += floor((float)$billValueStr / (float)$machine->coin_input_value);
                            }
                        }
                    }
                    $totalPlays = $playsFromCoins + $playsFromAssign + $playsFromBills;
                    $avgPayout = ((float)$machine->coin_input_value * self::GAMBLING_RTP) / (float)$machine->payout_unit_value;
                    for ($i = 0; $i < $totalPlays; $i++) {
                        $deltas['coin_out'] += rand(0, (int)($avgPayout * 2));
                    }
                }
                break;
            case 'simple_io':
                $deltas['credit_in'] = rand(...self::SIMPLE_COIN_INPUT_RANGE);
                if ($isAssignCreditTriggered && (float)$machine->coin_input_value > 0) {
                    $deltas['credit_in'] += floor((float)$machine->credit_button_value / (float)$machine->coin_input_value);
                }
                if ((float)$machine->payout_unit_value > 0) {
                    $payoutCount = floor($deltas['credit_in'] * self::SIMPLE_PAYOUT_RATIO);
                    for ($i = 0; $i < $payoutCount; $i++) {
                        $deltas['coin_out'] += rand(1, 3);
                    }
                }
                break;
            case 'input_only':
                if ($machine->bill_acceptor_enabled) {
                    $accepted = json_decode($machine->accepted_denominations, true) ?: [];
                    $billCount = rand(...self::INPUT_ONLY_BILL_INPUT_RANGE);
                    for ($i = 0; $i < $billCount; $i++) {
                        if (!empty($accepted)) {
                            $billValueStr = (string) $accepted[array_rand($accepted)];
                            if (isset(self::DENOMINATION_VALUE_TO_CODE[$billValueStr])) {
                                $deltas['bill_denomination'] += self::DENOMINATION_VALUE_TO_CODE[$billValueStr];
                            }
                        }
                    }
                }
                $deltas['assign_credit'] = 0;
                break;
        }
        return $deltas;
    }

    /**
     * 輔助函式：根據選項獲取目標機台
     */
    private function getTargetMachines()
    {
        $machineIds = $this->option('machine');
        $machineQuery = Machine::query()->where('is_active', true);

        if (!empty($machineIds)) {
            $machineQuery->whereIn('id', $machineIds);
        } else {
            $this->info("未指定特定機台，將處理所有活動中的機台...");
        }

        $machines = $machineQuery->get();
        if ($machines->isEmpty()) {
            $this->warn('找不到任何符合條件的活動機台。');
        }
        return $machines;
    }

    /**
     * 輔助函式：根據 machine_type 返回其行為範本
     */
    private function getBehaviorTemplate(?string $machineType): string
    {
        if (!$machineType) return 'unknown';
        $map = [
            'pinball' => 'pinball_like',
            'pachinko' => 'pinball_like',
            'claw_machine' => 'claw_like',
            'giant_claw_machine' => 'claw_like',
            'stacker_machine' => 'claw_like',
            'slot_machine' => 'gambling_like',
            'gambling' => 'gambling_like',
            'normally' => 'simple_io',
            'racing_game' => 'simple_io',
            'light_gun_game' => 'simple_io',
            'dance_game' => 'simple_io',
            'basketball_game' => 'simple_io',
            'air_hockey' => 'simple_io',
            'beat_em_up' => 'simple_io',
            'light_and_sound_game' => 'simple_io',
            'labyrinth_game' => 'simple_io',
            'flight_simulator' => 'simple_io',
            'punching_machine' => 'simple_io',
            'water_shooting_game' => 'simple_io',
            'mini_golf_game' => 'simple_io',
            'interactive_dance_game' => 'simple_io',
            'electronic_shooting_game' => 'simple_io',
            'arcade_music_game' => 'simple_io',
            'money_slot' => 'input_only',
        ];
        return $map[$machineType] ?? 'unknown';
    }

    /**
     * 輔助函式：獲取一個所有計數器都為0的陣列
     */
    private function getZeroedCounters(): array
    {
        return ['credit_in' => 0, 'assign_credit' => 0, 'ball_in' => 0, 'ball_out' => 0, 'coin_out' => 0, 'bill_denomination' => 0];
    }
}
