<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_auth_keys', function (Blueprint $table) {
            $table->id();
            $table->string('auth_key')->unique()->comment('系統生成的驗證金鑰');
            $table->string('chip_hardware_id')->unique()->nullable()->comment('ESP32 硬體 ID (用戶填寫)');
            $table->timestamp('expires_at')->nullable()->comment('金鑰過期時間');

            $table->unsignedBigInteger('owner_id')->comment('擁有此金鑰/機器的用戶 ID');
            $table->unsignedBigInteger('created_by')->nullable()->comment('創建此記錄的用戶 ID');

            $table->string('status')->default('pending')->comment('金鑰狀態');
            $table->boolean('printed')->default(false)->comment('是否已打印');
            $table->timestamps();
            $table->softDeletes();

            // 外鍵約束 (暫時移除，將在單獨的遷移中添加)
            // $table->foreign('machine_id')->references('id')->on('machines')->onDelete('set null');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('machine_auth_keys', function (Blueprint $table) {
            // 移除此外鍵（如果存在，將在單獨的遷移中處理）
            // $table->dropForeign(['machine_id']);
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['created_by']);
        });
        Schema::dropIfExists('machine_auth_keys');
    }
};
