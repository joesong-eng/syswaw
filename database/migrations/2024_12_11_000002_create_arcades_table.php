<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArcadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arcades', function (Blueprint $table) {
            $table->id(); // 主鍵
            $table->string('name'); // 商鋪名稱
            $table->unsignedBigInteger('owner_id'); // 店主 ID
            $table->unsignedBigInteger('manager')->nullable(); // 管理員 ID
            $table->string('authorize_key')->nullable(); // 創建金鑰
            $table->string('address')->nullable(); // 商鋪地址
            $table->string('phone')->nullable(); // 商鋪電話
            $table->string('image_url')->nullable(); // 圖片網址
            $table->string('business_hours')->nullable(); // 營業時間
            $table->decimal('revenue_split', 5, 2)->default(0.45); // 店鋪分成比例
            $table->boolean('is_active')->default(true); // 是否啟用
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者 ID
            $table->string('type')->default('physical'); // Arcade type, default to 'Physical'
            $table->timestamps(); // 創建和更新時間
            $table->softDeletes(); // 軟刪除

            // 外鍵約束
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('arcades', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('arcades');
    }
}
