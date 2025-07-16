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
            $table->string('machine_id');
            $table->string('token');
            $table->integer('ball_in');
            $table->integer('ball_out');
            $table->integer('credit_in');
            $table->timestamp('timestamp');
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
        Schema::dropIfExists('machine_data_records');
    }
};
