<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineAuthKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 獲取 arcades 和 users
        $arcadeIds = DB::table('arcades')->pluck('id')->toArray();
        // 只獲取 'arcade-owner' 和 'machine-owner' 角色的使用者 ID
        $ownerEmails = ['yd60@tg25.win', 'yd65@tg25.win'];
        $userIds = DB::table('users')->whereIn('email', $ownerEmails)->pluck('id')->toArray();

        if (empty($arcadeIds)) {
            Log::warning('No arcade IDs found, defaulting to 1');
            $arcadeIds = [1];
        }
        if (empty($userIds)) {
            // 如果找不到指定的 owner 使用者，拋出錯誤，因為後續邏輯會失敗
            throw new \Exception('Machine owner users (yd60@tg25.win, yd65@tg25.win) not found. Please ensure UsersTableSeeder has been run correctly.');
        }

        // 機台配置（修正 auth_key 和 chip_hardware_id）
        $machines = [
            [
                'auth_key' => '4c0c6435',
                'chip_hardware_id' => 'iot001',
                'name' => 'Pinball Machine 1',
                'machine_type' => 'pinball',
                'payout_unit_value' => 1.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'ball',
            ],
            [
                'auth_key' => '4283c91d',
                'chip_hardware_id' => 'iot002',
                'name' => 'Pinball Machine 2',
                'machine_type' => 'pinball',
                'payout_unit_value' => 1.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'ball',
            ],
            [
                'auth_key' => '1278a3b5',
                'chip_hardware_id' => 'iot003',
                'name' => 'Pinball Machine 3',
                'machine_type' => 'pinball',
                'payout_unit_value' => 1.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'ball',
            ],
            [
                'auth_key' => '52ad9cd5',
                'chip_hardware_id' => 'iot004',
                'name' => 'Lottery Machine 1',
                'machine_type' => 'lottery',
                'payout_unit_value' => 10.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'tickets',
            ],
            [
                'auth_key' => '7b9e2f1a',
                'chip_hardware_id' => 'iot005',
                'name' => 'Lottery Machine 2',
                'machine_type' => 'lottery',
                'payout_unit_value' => 10.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'tickets',
            ],
            [
                'auth_key' => '3c4d8e6b',
                'chip_hardware_id' => 'iot006',
                'name' => 'Lottery Machine 3',
                'machine_type' => 'lottery',
                'payout_unit_value' => 10.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'tickets',
            ],
            [
                'auth_key' => '9a1f5c2d',
                'chip_hardware_id' => 'iot007',
                'name' => 'Bill Machine 1',
                'machine_type' => 'bill',
                'payout_unit_value' => 0.0,
                'bill_acceptor_enabled' => true,
                'bill_currency' => 'TWD',
                'accepted_denominations' => [100, 200, 500, 1000],
                'payout_type' => 'none',
            ],
            [
                'auth_key' => '6e3b7f9a',
                'chip_hardware_id' => 'iot008',
                'name' => 'Bill Machine 2',
                'machine_type' => 'bill',
                'payout_unit_value' => 0.0,
                'bill_acceptor_enabled' => true,
                'bill_currency' => 'TWD',
                'accepted_denominations' => [100, 200, 500, 1000],
                'payout_type' => 'none',
            ],
            [
                'auth_key' => '2d5a1c8e',
                'chip_hardware_id' => 'iot009',
                'name' => 'Gambling Machine 1',
                'machine_type' => 'gambling',
                'payout_unit_value' => 100.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'none',
            ],
            [
                'auth_key' => '8f6b3e4d',
                'chip_hardware_id' => 'iot010',
                'name' => 'Gambling Machine 2',
                'machine_type' => 'gambling',
                'payout_unit_value' => 100.0,
                'bill_acceptor_enabled' => false,
                'bill_currency' => null,
                'accepted_denominations' => null,
                'payout_type' => 'none',
            ],
        ];

        foreach ($machines as $machineData) {
            // 檢查或更新 MachineAuthKey
            $authKey = MachineAuthKey::updateOrCreate(
                ['auth_key' => $machineData['auth_key']],
                [
                    'chip_hardware_id' => $machineData['chip_hardware_id'],
                    'owner_id' => $userIds[array_rand($userIds)],
                    'created_by' => $userIds[array_rand($userIds)],
                    'status' => 'active',
                    'printed' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            Log::info("Processed MachineAuthKey", [
                'auth_key' => $machineData['auth_key'],
                'chip_hardware_id' => $authKey->chip_hardware_id,
                'machine_id' => $authKey->machine_id,
            ]);

            // 檢查或更新 Machine
            $machine = Machine::updateOrCreate(
                ['auth_key_id' => $authKey->id],
                [
                    'name' => $machineData['name'],
                    'arcade_id' => $arcadeIds[array_rand($arcadeIds)],
                    'owner_id' => $userIds[array_rand($userIds)],
                    'created_by' => $userIds[array_rand($userIds)],
                    'is_active' => true,
                    'bill_acceptor_enabled' => $machineData['bill_acceptor_enabled'],
                    'bill_currency' => $machineData['bill_currency'],
                    'accepted_denominations' => $machineData['accepted_denominations']
                        ? json_encode($machineData['accepted_denominations'])
                        : null,
                    'ui_language' => 'zh-TW',
                    'auto_shutdown_seconds' => 300,
                    'status' => json_encode([
                        'operational' => true,
                        'last_maintenance' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                    ]),
                    'revenue_split' => 70.0,
                    'machine_type' => $machineData['machine_type'],
                    'share_pct' => 0.20,
                    'coin_input_value' => 10.0,
                    'credit_button_value' => $machineData['machine_type'] === 'gambling' ? 10.0 : null,
                    'payout_button_value' => $machineData['machine_type'] === 'gambling' ? 10.0 : null,
                    'payout_type' => $machineData['payout_type'],
                    'payout_unit_value' => $machineData['payout_unit_value'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            Log::info("Processed Machine", [
                'name' => $machineData['name'],
                'auth_key_id' => $authKey->id,
                'machine_id' => $machine->id,
                'machine_type' => $machineData['machine_type'],
                'bill_currency' => $machineData['bill_currency'],
                'accepted_denominations' => $machineData['accepted_denominations']
                    ? json_encode($machineData['accepted_denominations'])
                    : null,
            ]);

            // 更新 MachineAuthKey 的 machine_id
            if ($authKey->machine_id != $machine->id) {
                $authKey->update(['machine_id' => $machine->id]);
                Log::info("Updated MachineAuthKey machine_id", [
                    'auth_key' => $machineData['auth_key'],
                    'machine_id' => $machine->id,
                ]);
            }
        }
    }
}
