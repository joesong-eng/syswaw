<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ArcadeKey;
use App\Models\Arcade; // 引入 Arcade 模型
use App\Models\User; // 引入 User 模型
use Illuminate\Support\Str; // 引入 Str 輔助函數生成 key_value

class ArcadeKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure arcades and admin user exist.
        // The actual seeding order should be managed in DatabaseSeeder.
        Arcade::where('name', '台北旗艦店')->firstOrFail();
        Arcade::where('name', '台中概念店')->firstOrFail();
        $adminUser = User::where('email', 'admin@tg25.win')->firstOrFail();

        // 創建範例街機金鑰數據
        ArcadeKey::firstOrCreate(
            ['token' => 'ARCADE_KEY_TAIPEI_001'], // 使用 token 進行查找
            [
                'token' => 'ARCADE_KEY_TAIPEI_001', // 實際的 token
                'created_by' => $adminUser->id,
                'expires_at' => now()->addYears(10), // 設置一個較長的過期時間
                'used' => false,
                'authenticatable_id' => 0, // 預設值
                'authenticatable_type' => null, // 預設值
            ]
        );

        ArcadeKey::firstOrCreate(
            ['token' => 'ARCADE_KEY_TAICHUNG_001'],
            [
                'token' => 'ARCADE_KEY_TAICHUNG_001',
                'created_by' => $adminUser->id,
                'expires_at' => now()->addYears(10),
                'used' => false,
                'authenticatable_id' => 0,
                'authenticatable_type' => null,
            ]
        );

        // 可以添加更多街機金鑰數據
    }
}
