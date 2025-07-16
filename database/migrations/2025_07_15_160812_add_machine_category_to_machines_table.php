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
        Schema::table('machines', function (Blueprint $table) {
            $table->string('machine_category')->nullable()->after('machine_type')->comment('核心營運模式分類 (pure_game, redemption, gambling, etc.)');
            $table->index('machine_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropIndex(['machine_category']);
            $table->dropColumn('machine_category');
        });
    }
};
