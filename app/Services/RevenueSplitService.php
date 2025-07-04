<?php

namespace App\Services;

use App\Contracts\PlatformUserFinderInterface;
use App\Models\Machine;
use App\Models\SystemSetting;

class RevenueSplitService
{
    protected PlatformUserFinderInterface $platformUserFinder;

    public function __construct(PlatformUserFinderInterface $platformUserFinder)
    {
        $this->platformUserFinder = $platformUserFinder;
    }

    public function calculateShares(float $totalRevenue, Machine $machine): array
    {
        $shares = [];

        $platformPct = (float) (SystemSetting::where('setting_key', 'platform_share_pct')->first()->setting_value ?? 0.05);
        $arcadePct = (float) (SystemSetting::where('setting_key', 'arcade_default_share_pct')->first()->setting_value ?? 0.55);
        $machinePct = (float) (SystemSetting::where('setting_key', 'machine_default_share_pct')->first()->setting_value ?? 0.40);

        if (abs($platformPct + $arcadePct + $machinePct - 1.0) > 0.01) {
            throw new \Exception("分潤比例總和必須為 100%，當前為: {$platformPct}, {$arcadePct}, {$machinePct}");
        }

        $platformAdmin = $this->platformUserFinder->findPlatformAdmin();
        $platformUserId = $platformAdmin?->id;

        $shares['platform'] = [
            'user_id' => $platformUserId,
            'amount' => round($totalRevenue * $platformPct, 2),
            'role' => 'platform',
        ];

        $arcadeOwnerUserId = $machine->arcade?->owner_id ?? null;
        $shares['arcade_owner'] = [
            'user_id' => $arcadeOwnerUserId,
            'amount' => round($totalRevenue * $arcadePct, 2),
            'role' => 'arcade-owner',
        ];

        $machineOwnerUserId = $machine->owner_id ?? null;
        $shares['machine_owner'] = [
            'user_id' => $machineOwnerUserId,
            'amount' => round($totalRevenue * $machinePct, 2),
            'role' => 'machine-owner',
        ];

        return $shares;
    }
}
