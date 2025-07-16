<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MachineAuthKeySeeder extends Seeder
{
    public function run(): void
    {
        // 禁用外鍵約束
        Schema::disableForeignKeyConstraints();
        // 清空相關表
        // \DB::table('bill_records')->truncate();
        // \DB::table('machine_data_extended')->truncate();
        // \DB::table('machine_data_records')->truncate();
        // \DB::table('machine_data')->truncate();
        // \DB::table('machines')->truncate();
        \DB::table('machine_auth_keys')->truncate();

        $userIds = \DB::table('users')->pluck('id')->toArray();
        if (empty($userIds)) {
            throw new \Exception('users 表至少需要 4 筆使用者數據，請先運行 UsersSeeder，當前有： ' . count($userIds) . ' 筆。');
        }

        // 與 MachineSeeder 一致的 auth_key 和 chip_hardware_id
        $authKeys = [
            ['auth_key' => '4c0c6435', 'chip_hardware_id' => 'iot001'],
            ['auth_key' => '4283c91d', 'chip_hardware_id' => 'iot002'],
            ['auth_key' => '1278a3b5', 'chip_hardware_id' => 'iot003'],
            ['auth_key' => '52ad9cd5', 'chip_hardware_id' => 'iot004'],
            ['auth_key' => '7b9e2f1a', 'chip_hardware_id' => 'iot005'],
            ['auth_key' => '3c4d8e6b', 'chip_hardware_id' => 'iot006'],
            ['auth_key' => '9a1f5c2d', 'chip_hardware_id' => 'iot007'],
            ['auth_key' => '6e3b7f9a', 'chip_hardware_id' => 'iot008'],
            ['auth_key' => '2d5a1c8e', 'chip_hardware_id' => 'iot009'],
            ['auth_key' => '8f6b3e4d', 'chip_hardware_id' => 'iot010'],
            ['auth_key' => 'CbbGYRvk', 'chip_hardware_id' => 'iot011'],
            ['auth_key' => '6g1R6WNJ', 'chip_hardware_id' => 'iot012'],
            ['auth_key' => 'qNBSSm0k', 'chip_hardware_id' => 'iot013'],
        ];

        foreach ($authKeys as $keyData) {
            \DB::table('machine_auth_keys')->insert([
                'auth_key' => $keyData['auth_key'],
                'chip_hardware_id' => $keyData['chip_hardware_id'],
                'expires_at' => now()->addYear(), // 金鑰有效期 1 年
                'machine_id' => null, // 尚未關聯機器
                'owner_id' => $userIds[array_rand($userIds)], // 隨機選取使用者
                'created_by' => $userIds[array_rand($userIds)], // 隨機選取使用者
                'status' => 'pending',
                'printed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 恢復外鍵約束
        Schema::enableForeignKeyConstraints();
    }
}
