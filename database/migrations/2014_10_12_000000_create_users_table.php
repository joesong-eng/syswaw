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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            // 從 add_two_factor_columns_to_users_table.php 合併過來
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_member')->default(false); // 是否為會員
            $table->boolean('is_active')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->foreignId('current_team_id')->nullable(); // 新增 current_team_id
            $table->string('profile_photo_path', 2048)->nullable(); // 新增 profile_photo_path
            $table->unsignedBigInteger('created_by')->nullable(); // 記錄創建者 ID
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->rememberToken();
            // 從 add_sidebar_permissions_to_users_table.php 合併過來 (您目前正在看的檔案)
            $table->json('sidebar_permissions')->nullable();
            // 從 add_invitation_code_to_users.php 合併過來
            $table->string('invitation_code')->nullable()->unique()->comment('邀請碼');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null'); // 外鍵
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
