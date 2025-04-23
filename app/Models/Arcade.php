<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Machine;


class Arcade extends Model
{
    use HasFactory;

    protected $table = 'arcades';
    protected $fillable = ['name', 'owner_id', 'authorize_key', 'manager', 'address', 'phone', 'image_url', 'type', 'is_active'];
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
}