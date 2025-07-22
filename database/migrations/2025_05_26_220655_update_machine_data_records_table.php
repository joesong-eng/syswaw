<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 第一步：新增新欄位並設置索引
        Schema::table('machine_data_records', function (Blueprint $table) {
            $table->integer('coin_out')->default(0)->comment('贈獎數')->after('credit_in');
        });

        // 第二步：遷移現有 ball_in, ball_out 數據到 machine_data_extended
        $records = DB::table('machine_data_records')->get();
        foreach ($records as $record) {
            if ($record->ball_in > 0) {
                DB::table('machine_data_extended')->insert([
                    'record_id' => $record->id,
                    'data_type' => 'ball_in',
                    'value' => $record->ball_in,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if ($record->ball_out > 0) {
                DB::table('machine_data_extended')->insert([
                    'record_id' => $record->id,
                    'data_type' => 'ball_out',
                    'value' => $record->ball_out,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // 假設現有數據主要來自彈珠台，設置 machine_type 為 pinball
            DB::table('machine_data_records')
                ->where('id', $record->id)
                ->where(function ($query) {
                    $query->where('ball_in', '>', 0)->orWhere('ball_out', '>', 0);
                })
                ->update(['machine_type' => 'pinball']);
        }

        // 第三步：移除舊欄位 (如果存在)
        // 由於 ball_in 和 ball_out 在 create_machine_data_records_table 中被註釋掉，這裡不需要刪除
    }

    public function down()
    {
        // 恢復舊欄位 (如果存在)
        // 由於 ball_in 和 ball_out 在 create_machine_data_records_table 中被註釋掉，這裡不需要恢復

        // 移除新增欄位和索引
        Schema::table('machine_data_records', function (Blueprint $table) {
            $table->dropColumn(['coin_out']);
        });
    }
};
