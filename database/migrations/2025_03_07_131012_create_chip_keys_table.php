<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChipKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chip_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->timestamp('expires_at');
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->constrained('users'); // 與 users 表做關聯
            $table->string('status');
            $table->boolean('printed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chip_keys', function (Blueprint $table) {
            // 刪除外鍵約束
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['created_by']);
        });

        // 刪除整個表
        Schema::dropIfExists('chip_keys');
    }
}