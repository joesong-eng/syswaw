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
        Schema::create('monthly_report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_report_id')->constrained('monthly_reports')->onDelete('cascade');

            // 這筆分潤是關於誰的？
            $table->morphs('reportable'); // 可以是 Arcade, User (Machine Owner) 等模型

            // 這筆分潤的詳細數據
            $table->decimal('total_revenue', 12, 2)->comment('該對象產生的總營收');
            $table->decimal('total_cost', 12, 2)->comment('該對象產生的總成本');
            $table->decimal('net_profit', 12, 2)->comment('該對象的淨利');
            $table->decimal('revenue_share_amount', 12, 2)->comment('應分潤金額');
            $table->decimal('platform_share_amount', 12, 2)->comment('平台從中抽成的金額');

            $table->json('source_data')->nullable()->comment('儲存計算來源的快照，例如包含的 machine_id 列表');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_report_details');
    }
};
