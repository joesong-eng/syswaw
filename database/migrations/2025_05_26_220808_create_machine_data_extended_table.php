<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('machine_data_extended', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->comment('關聯到 machine_data_records');
            $table->string('data_type')->comment('數據類型: ball_in, ball_out, assign_credit, settled_credit');
            $table->integer('value')->default(0)->comment('數據值');
            $table->index('data_type');
            $table->timestamps();

            $table->foreign('record_id')
                ->references('id')
                ->on('machine_data_records')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('machine_data_extended');
    }
};
