<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(0); // 層級欄位，預設為 0
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
