<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // 確保角色已經存在
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $userRole = Role::where('name', 'user')->firstOrFail();

        // 創建管理員用戶
        if (!User::where('email', 'admin@tg25.win')->exists()) {
            $adminUser = User::create([
                'name' => 'admin',
                'email' => 'admin@tg25.win',
                'password' => Hash::make('we123123'),
                'email_verified_at' => now(),
            ]);
            $adminUser->assignRole($adminRole->name); // 使用角色的名稱
        }

        // 創建普通用戶
        if (!User::where('email', 'user@tg25.win')->exists()) {
            $normalUser = User::create([
                'name' => 'user',
                'email' => 'user@tg25.win',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $normalUser->assignRole($userRole->name); // 使用角色的名稱
        }

        if (!User::where('email', 'admin@tg25.win')->exists()) {
            Log::info('Admin user created: admin@tg25.win');
        } else {
            Log::info('Admin user already exists: admin@tg25.win');
        }

        $this->command->info('Admin user created: admin@tg25.win');
    }
}
