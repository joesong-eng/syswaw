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
            $table->string('phone')->nullable();
            $table->boolean('is_member')->default(false); // 是否為會員
            $table->boolean('is_active')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable(); // 記錄創建者 ID
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->rememberToken();
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
