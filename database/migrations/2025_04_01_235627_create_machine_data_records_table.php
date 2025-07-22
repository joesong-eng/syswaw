<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_data_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_key_id')->nullable()->comment('關聯到 machine_auth_keys');
            $table->string('token')->nullable()->comment('設備 token');
            $table->string('machine_type')->nullable()->comment('機型: pinball, lottery, doll, gambling, bill');
            $table->integer('credit_in')->default(0)->comment('投幣數（硬幣或紙鈔總額）');
            // $table->integer('coin_out')->default(0)->comment('贈獎數');
            $table->integer('return_value')->default(0)->comment('退等值物品的價值（彩票/禮物）');
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            $table->foreign('auth_key_id')
                ->references('id')
                ->on('machine_auth_keys')
                ->onDelete('cascade');
            $table->index('machine_type');
            $table->index('timestamp');
        });
    }

    public function down()
    {
        Schema::dropIfExists('machine_data_records');
    }
};
