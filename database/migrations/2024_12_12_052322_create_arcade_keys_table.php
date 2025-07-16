<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arcade_keys', function (Blueprint $table) {
            $table->id(); // 主鍵
            $table->string('token')->unique(); // 金鑰
            $table->timestamp('expires_at'); // 過期時間
            $table->boolean('used')->default(false); // 是否已使用

            // 新增多態關聯欄位
            $table->unsignedBigInteger('authenticatable_id')->default(0); // 關聯模型的主鍵 ID
            $table->string('authenticatable_type')->nullable(); // 關聯模型的類型（如 Store 或 Machine）

            // 新增索引
            $table->index(['authenticatable_id', 'authenticatable_type'], 'authenticatable_index');

            // 新增 created_by 欄位並設置外鍵
            $table->unsignedBigInteger('created_by'); // 新增的用戶 ID
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arcade_keys', function (Blueprint $table) {
            // 刪除 created_by 的外鍵
            $table->dropForeign(['created_by']);
            // 刪除多態關聯索引
            $table->dropIndex('authenticatable_index');
        });

        // 刪除整個表
        Schema::dropIfExists('arcade_keys');
    }
};
