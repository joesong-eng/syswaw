<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Machines extends Model{
    use HasFactory;

    protected $table = 'machines';

    protected $fillable = [
        'storeable_id', 'storeable_type',
        'owner_id','create_by',
        'token', 
        'expires_at' ,
        'is_active', 
        'revenue_split',
        'machine_type', 
        'machine_id',
        'name', 'used' , 'status',
    ];
   
    // 使用多態關聯 'create_by', 'expires_at',

    // 創建者
    public function creator(){
        return $this->belongsTo(User::class, 'create_by');
    }
    // API 金鑰的多態關聯
    public function apiKeys()
    {
        return $this->morphMany(ApiKey::class, 'authenticatable');
    }

    // 多態關聯到 Store 或 VsStore

    public function storeable() {
        return $this->morphTo();  // 多態關聯
    }
    // 機器所有者
    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    // 機器管理員
    public function managers()
    {
        return $this->belongsToMany(User::class, 'machine_managers');
    }
}
