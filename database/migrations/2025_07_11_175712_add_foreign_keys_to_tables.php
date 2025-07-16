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
            // 添加 auth_key_id 列
            $table->foreignId('auth_key_id')->nullable()->unique()->after('created_by');
            // 添加外鍵約束
            $table->foreign('auth_key_id')->references('id')->on('machine_auth_keys')->onDelete('set null');
        });

        Schema::table('machine_auth_keys', function (Blueprint $table) {
            // 添加 machine_id 列
            $table->unsignedBigInteger('machine_id')->nullable()->after('expires_at');
            // 添加外鍵約束
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropForeign(['auth_key_id']);
            $table->dropColumn('auth_key_id');
        });

        Schema::table('machine_auth_keys', function (Blueprint $table) {
            $table->dropForeign(['machine_id']);
            $table->dropColumn('machine_id');
        });
    }
};
