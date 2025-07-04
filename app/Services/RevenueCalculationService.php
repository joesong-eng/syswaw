<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\MachineDataRecord;
use App\Models\SystemSetting;
use Carbon\Carbon;

class RevenueCalculationService
{
    protected $defaultCreditValue;

    public function __construct()
    {
        // 在建構函數中獲取一次預設 credit value，減少重複查詢
        $this->defaultCreditValue = (float) (SystemSetting::where('setting_key', 'default_credit_value')->first()->setting_value ?? 10.00);
    }

    /**
     * 計算指定機器在指定時間段內的總營收。
     *
     * @param Machine $machine
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function calculateForMachine(Machine $machine, Carbon $startDate, Carbon $endDate): float
    {
        $creditValue = (float) ($machine->credit_value ?? $this->defaultCreditValue);

        $totalCredits = MachineDataRecord::whereHas('machineAuthKey', function ($query) use ($machine) {
            $query->where('machine_id', $machine->id);
        })
            ->whereBetween('timestamp', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('credit_in');

        return (float) $totalCredits * $creditValue;
    }

    /**
     * 計算多台機器在指定時間段內的總營收。
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $machines
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array ['machine_id' => revenue, ...]
     */
    public function calculateForMultipleMachines($machines, Carbon $startDate, Carbon $endDate): array
    {
        $creditValue = (float) ($machine->credit_value ?? 10.00); // 這裡保留預設值

        $totalCredits = MachineDataRecord::whereHas('machineAuthKey', function ($query) use ($machine) {
            $query->where('machine_id', $machine->id);
        })
            ->whereBetween('timestamp', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('credit_in');

        return (float) $totalCredits * $creditValue;
    }
}
