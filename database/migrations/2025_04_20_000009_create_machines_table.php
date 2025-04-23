<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('arcade_id'); // 對應 Arcade 的 id
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('chip_id')->nullable()->unique();
            $table->string('machine_type')->nullable();
            $table->json('status')->nullable();
            $table->boolean('is_active')->default(false);
            $table->decimal('revenue_split', 5, 2)->default(0.45);
            $table->timestamps();
            $table->softDeletes();
    
            // 外鍵約束
            $table->foreign('arcade_id')->references('id')->on('arcades')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('chip_id')->references('id')->on('chip_keys')->onDelete('set null');

        });
    }

    public function down()
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropForeign(['arcade_id']); // 移除新增的外鍵
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['chip_id']);
        });
        Schema::dropIfExists('machines');
    }
};