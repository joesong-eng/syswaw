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
<<<<<<<< HEAD:database/migrations.bak/2025_07_10_095040_add_redis_stream_id_to_machine_data_table.php
        Schema::table('machine_data', function (Blueprint $table) {
            $table->string('redis_stream_id', 50)->nullable()->after('timestamp');
            $table->unique('redis_stream_id');
========
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id');
            $table->foreignId('user_id');
            $table->string('role')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
>>>>>>>> ee952fa (完成階段性工作，系統正常運作):database/migrations/2020_05_21_200000_create_team_user_table.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations.bak/2025_07_10_095040_add_redis_stream_id_to_machine_data_table.php
        Schema::table('machine_data', function (Blueprint $table) {
            $table->dropUnique(['redis_stream_id']);
            $table->dropColumn('redis_stream_id');
        });
========
        Schema::dropIfExists('team_user');
>>>>>>>> ee952fa (完成階段性工作，系統正常運作):database/migrations/2020_05_21_200000_create_team_user_table.php
    }
};
