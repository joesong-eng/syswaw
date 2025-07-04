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
        Schema::create('roles', function (Blueprint $table) {
            $table->id(); // 自動遞增的 ID
            $table->string('name');
            $table->string('guard_name');
            $table->unsignedInteger('level')->default(0); // 層級欄位，預設為 0
            $table->timestamps();
            $table->unique(['name', 'guard_name']); // 確保 name 和 guard_name 的組合是唯一的
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
