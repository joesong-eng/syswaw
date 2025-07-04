<?php
// database/migrations/2025_05_26_220841_create_bill_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bill_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->comment('關聯到 machine_data_records');
            $table->integer('bill_denomination')->comment('紙鈔面額，如 100, 500, 1000');
            $table->integer('bill_count')->default(1)->comment('該面額的紙鈔數量');
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            $table->foreign('record_id')
                ->references('id')
                ->on('machine_data_records')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bill_records');
    }
};
