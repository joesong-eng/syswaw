<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('arcade_id')->nullable()->constrained('arcades')->onDelete('set null');
            $table->foreignId('owner_id')->nullable()->comment('遊戲機擁有者 User ID')->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('auth_key_id')->nullable()->unique()->constrained('machine_auth_keys')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            // 紙鈔機相關設定
            $table->boolean('bill_acceptor_enabled')->default(false)->comment('是否啟用紙鈔接收功能');
            $table->string('bill_currency')->nullable()->comment('紙鈔幣別（例如 TWD, USD, JPY）');
            $table->json('accepted_denominations')->nullable()->comment('可接受的面額陣列，例如 [100, 500, 1000]');
            $table->unsignedTinyInteger('volume_level')->default(5);
            $table->string('ui_language')->default('zh-TW');
            $table->unsignedInteger('auto_shutdown_seconds')->default(300);
            $table->json('status')->nullable()->comment('機台狀態詳細，例如 {"operational": true, "last_maintenance": "YYYY-MM-DD"}');
            $table->decimal('revenue_split', 5, 1)->nullable()->comment('機器獲得的收益比例');
            $table->string('machine_type')->default('pinball')->comment('遊戲機類型: pinball, lottery, doll, gambling, bill');
            $table->decimal('share_pct', 5, 2)->nullable()->comment('平台對此機台的抽成百分比');
            // 價值相關設定
            $table->decimal('coin_input_value', 10, 2)->nullable()->comment('每枚投入代幣/錢幣的價值，NULL 表示使用系統預設');
            $table->decimal('payout_unit_value', 10, 2)->nullable()->comment('每單位退還物的價值');
            $table->decimal('credit_button_value', 10, 2)->nullable()->comment('開分鍵：按一次增加的貨幣價值');
            $table->decimal('payout_button_value', 10, 2)->nullable()->comment('洗分鍵：按一次移除/結算的貨幣價值');
            // 通用退還設定
            $table->string('payout_type')->nullable()->comment('退還物類型，例如 points, tickets, coins, ball, prize, none');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropForeign(['arcade_id']); // 移除新增的外鍵
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['chip_id']);
        });
        Schema::dropIfExists('machines');
    }
};
