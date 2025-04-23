<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create temp_machine_transactions table
        Schema::create('temp_transactions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('transaction_id')->unique(); // Unique transaction ID
            $table->string('machine_id'); // Machine ID
            $table->string('token'); // Token for authentication
            $table->decimal('credit_in', 10, 2); // Positive for in, negative for out
            $table->decimal('ball_in', 10, 2); // Number of balls inserted
            $table->decimal('ball_out', 10, 2); // Number of balls ejected
            $table->string('source')->nullable(); // Source of the transaction
            $table->timestamps(); // Created and updated timestamps
        });

        // Create machine_transactions table
        Schema::create('machine_transactions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('transaction_id')->unique(); // Unique transaction ID
            $table->string('machine_id'); // Machine ID
            $table->string('token'); // Token for authentication
            $table->decimal('credit_in', 10, 2); // Positive for in, negative for out
            $table->decimal('ball_in', 10, 2); // Number of balls inserted
            $table->decimal('ball_out', 10, 2); // Number of balls ejected
            $table->string('source')->nullable(); // Source of the transaction

            // Polymorphic relationship to store (storeable)
            $table->unsignedBigInteger('storeable_id'); // Store ID
            $table->string('storeable_type'); // Store type (Store or VsStore)

            $table->unsignedBigInteger('owner_id'); // Owner ID (machine owner)
            $table->timestamps(); // Created and updated timestamps

            // Foreign key constraints
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_transactions');
        Schema::dropIfExists('machine_transactions');
    }
};
