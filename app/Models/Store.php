<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;

    protected $table = 'arcades';
    protected $fillable = ['name', 'owner_id', 'managers', 'address', 'phone', 'expires_at', 'image_url',];
    // 創建者
    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }
    // 店鋪所有者
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    // 店鋪管理員
    public function managers()
    {
        return $this->belongsToMany(User::class, 'store_managers', 'store_id', 'user_id')
            ->withPivot('token')->withTimestamps();
    }
    // 多態關聯的機器
    public function machines()
    {
        return $this->morphMany(Machine::class, 'storeable');
    }
}
