<?php
// app/Models/Arcade.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Machine;
use Illuminate\Support\Str; // Add this at the top


class Arcade extends Model
{
    use HasFactory;

    protected $table = 'arcades';
    protected $fillable = [ // 允許批量賦值的欄位
        'name',                 // 店名
        'owner_id',             // 店主 ID
        'manager',              // 經理 ID
        'authorize_key',        // 授權金鑰 ID (FK to arcade_keys)
        'authorization_code',   // 授權碼 (用於機台連接)
        'address',              // 地址
        'phone',                // 電話
        'currency',             // 幣種
        'share_pct',            // 平台分成比例
        'image_url',            // 圖片 URL
        'business_hours',       // 營業時間
        'is_active',            // 是否啟用
        'created_by',           // 創建者 ID
        'type',                 // 類型 (physical, virtual)
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'platform_share_pct' => 'decimal:2', // 將其轉換為帶兩位小數的浮點數
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;

    // 創建者
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by'); // 修正 'create_by' 為 'created_by'
    }

    // 店鋪所有者
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // 店鋪管理員
    public function managers()
    {
        return $this->belongsToMany(User::class, 'arcade_managers', 'arcade_id', 'user_id')
            ->withPivot('token')->withTimestamps();
    }

    // 修正：與 Machine 的一對多關聯
    public function machines()
    {
        return $this->hasMany(Machine::class, 'arcade_id');
    }

    public function arcadeKey()
    {
        return $this->belongsTo(ArcadeKey::class, 'authorize_key', 'id');
    }

    public function authorizeKey()
    {
        return $this->belongsTo(ArcadeKey::class, 'authorize_key', 'id');
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($arcade) {
            if (empty($arcade->authorization_code)) {
                $arcade->authorization_code = Str::random(12); // 生成一個12位的隨機授權碼
            }
        });
    }
}
