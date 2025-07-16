<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Arcade;
use App\Models\User; // 引入 User 模型

class ArcadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assuming UsersTableSeeder runs before this seeder, we can query for the admin user.
        $adminUser = User::where('email', 'admin@tg25.win')->firstOrFail();

        // 創建範例街機數據
        Arcade::firstOrCreate(
            ['name' => '台北旗艦店'],
            [
                'owner_id' => $adminUser->id,
                'manager' => $adminUser->id,
                'authorize_key' => 'TAIPEI_001',
                'address' => '台北市信義區信義路100號',
                'phone' => '02-12345678',
                'business_hours' => '10:00-22:00',
                'revenue_split' => 0.45,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'type' => 'physical',
            ]
        );

        Arcade::firstOrCreate(
            ['name' => '台中概念店'],
            [
                'owner_id' => $adminUser->id,
                'manager' => $adminUser->id,
                'authorize_key' => 'TAICHUNG_001',
                'address' => '台中市西屯區台灣大道200號',
                'phone' => '04-87654321',
                'business_hours' => '11:00-23:00',
                'revenue_split' => 0.50,
                'is_active' => true,
                'created_by' => $adminUser->id,
                'type' => 'physical',
            ]
        );

        // 可以添加更多街機數據
    }
}
