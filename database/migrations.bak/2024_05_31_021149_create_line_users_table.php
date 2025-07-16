<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
<<<<<<<< HEAD:database/migrations.bak/2024_05_31_021149_create_line_users_table.php
    public function up() {
        Schema::create('line_users', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id')->unique();
            $table->string('website_user_id')->nullable();
========
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('name');
            $table->boolean('personal_team');
>>>>>>>> ee952fa (完成階段性工作，系統正常運作):database/migrations/2020_05_21_100000_create_teams_table.php
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations.bak/2024_05_31_021149_create_line_users_table.php
        Schema::dropIfExists('line_users');
========
        Schema::dropIfExists('teams');
>>>>>>>> ee952fa (完成階段性工作，系統正常運作):database/migrations/2020_05_21_100000_create_teams_table.php
    }
};
