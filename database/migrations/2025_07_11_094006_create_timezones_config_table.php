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
        Schema::create('timezones_config', function (Blueprint $table) {
            $table->id();
            $table->string('timezone_name')->unique()->comment('時區名稱，例如 Asia/Taipei');
            $table->boolean('is_active')->default(true)->comment('是否啟用此時區的定時任務');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timezones_config');
    }
};
