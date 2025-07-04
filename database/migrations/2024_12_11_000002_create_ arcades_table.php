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
        Schema::dropIfExists('arcades');

        Schema::create('arcades', function (Blueprint $table) {
            $table->id(); // 主鍵
            $table->string('name'); // 商鋪名稱
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade'); // 店主 ID
            $table->foreignId('manager')->nullable()->constrained('users')->onDelete('set null'); // 管理員 ID
            $table->string('authorize_key')->nullable()->comment('系統創建 arcade 時的金鑰');
            $table->string('authorization_code')->unique()->nullable()->comment('供授權驗證使用的代碼');
            $table->string('address')->nullable(); // 商鋪地址
            $table->string('phone')->nullable(); // 商鋪電話
            $table->string('currency', 3)->nullable(); // 例如 TWD, USD, JPY
            $table->decimal('share_pct', 5, 2)->nullable()->after('currency'); // 假設放在 currency 後面

            $table->string('image_url')->nullable(); // 圖片網址
            $table->string('business_hours')->nullable(); // 營業時間
            $table->boolean('is_active')->default(true); // 是否啟用
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // 創建者 ID
            $table->string('type')->default('physical'); // Arcade type, default to 'Physical'
            $table->timestamps(); // 創建和更新時間
            $table->softDeletes(); // 軟刪除
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arcades', function (Blueprint $table) {
            $table->dropColumn('platform_share_pct');
        });
    }
};
