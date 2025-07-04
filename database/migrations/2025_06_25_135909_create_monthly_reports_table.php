<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique()->comment('報表單號，例如：MR-202507');
            $table->date('period_start')->comment('報表統計區間開始日期');
            $table->date('period_end')->comment('報表統計區間結束日期');

            // 全平台總計數據
            $table->decimal('total_revenue', 15, 2)->comment('全平台總營收');
            $table->decimal('total_cost', 15, 2)->comment('全平台總成本');
            $table->decimal('total_net_profit', 15, 2)->comment('全平台總淨利');

            // 平台收益
            $table->decimal('platform_share', 15, 2)->comment('平台抽成總額');
            $table->decimal('platform_profit', 15, 2)->comment('平台最終收益');

            $table->string('status')->default('completed')->comment('報表狀態：completed, pending, archived');
            $table->timestamp('generated_at')->comment('報表生成時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
