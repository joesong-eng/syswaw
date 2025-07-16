<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. 機主分成比例表
     */
    public function up(): void
    {
        Schema::create('revenue_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('收款人 User ID')->constrained('users')->onDelete('cascade');
            $table->string('payee_role')->comment('收款時的角色 (e.g., arcade-owner, machine-owner, platform)');
            $table->decimal('amount', 15, 2)->comment('分潤金額');
            $table->string('currency', 3)->default('TWD')->comment('幣別');
            $table->date('period_start')->comment('結算週期開始日期');
            $table->date('period_end')->comment('結算週期結束日期');
            $table->json('source_details_json')->nullable()->comment('與此分潤相關的來源摘要 (JSON)');
            $table->string('status')->default('calculated')->comment('狀態 (e.g., calculated, approved)');
            $table->timestamp('calculated_at')->comment('計算完成時間');
            $table->timestamps();

            $table->index(['user_id', 'period_start', 'period_end']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_shares');
    }
};
