<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {

        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->command->error("Admin role not found. Please run RoleSeeder first or ensure the admin role exists.");
            return;
        }

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@tg25.win'], // 用 email 作為唯一識別來避免重複創建
            [
                'name' => 'Administrator',
                'password' => Hash::make('we123123'),
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
            ]
        );

        $adminUser->assignRole($adminRole);

        // 定義使用者資料，包含角色和父級關係
        // 先建立擁有者，這樣他們才能被指派為員工的父級。
        $ownerUsersData = [
            ['email' => 'yd60@tg25.win', 'name' => 'User 60', 'role' => 'arcade-owner'],
            ['email' => 'yd65@tg25.win', 'name' => 'User 65', 'role' => 'machine-owner'],
        ];

        $staffUsersData = [
            ['email' => 'yd61@tg25.win', 'name' => 'User 61', 'role' => 'arcade-staff', 'parent_email' => 'yd60@tg25.win'],
            ['email' => 'yd66@tg25.win', 'name' => 'User 66', 'role' => 'machine-staff', 'parent_email' => 'yd65@tg25.win'],
        ];

        // 建立擁有者使用者
        foreach ($ownerUsersData as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('we123123'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'parent_id' => $adminUser->id, // 擁有者的父級是 admin
                ]
            );
            $user->assignRole($userData['role']);
        }

        // 建立員工使用者並關聯到其父級
        foreach ($staffUsersData as $userData) {
            $parentUser = User::where('email', $userData['parent_email'])->first();

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('we123123'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'parent_id' => $parentUser->id ?? null,
                ]
            );
            $user->assignRole($userData['role']);
        }
        $this->command->info('Default users created and assigned roles successfully.');
    }
}
