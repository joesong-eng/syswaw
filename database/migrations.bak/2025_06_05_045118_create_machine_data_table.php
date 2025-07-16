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
        Schema::create('machine_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->foreignId('arcade_id')->nullable()->constrained('arcades')->onDelete('set null');
            $table->foreignId('auth_key_id')->constrained('machine_auth_keys')->onDelete('cascade');
            $table->string('machine_type');
            $table->unsignedBigInteger('credit_in')->default(0);
            $table->unsignedBigInteger('ball_in')->default(0);
            $table->unsignedBigInteger('ball_out')->default(0);
            $table->unsignedBigInteger('coin_out')->default(0);
            $table->unsignedBigInteger('assign_credit')->default(0);
            $table->unsignedBigInteger('settled_credit')->default(0);
            $table->unsignedBigInteger('bill_denomination')->default(0);
            $table->string('error_code')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
            $table->index(['arcade_id', 'machine_id', 'timestamp']);
            $table->index('timestamp');
            $table->index('machine_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_data');
    }
};
