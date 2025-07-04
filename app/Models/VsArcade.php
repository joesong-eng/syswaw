<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VsArcade extends Model
{
    use HasFactory;
    // <!-- 這張表可能其實不需要了 -->
    protected $table = 'vs_arcades';
    protected $fillable = [
        'name',
        'managers',
        'address',
        'phone',
        'business_hours',
        'revenue_split',
        'is_active',
        'created_by',
        'owner_id',
    ];

    // 創建者
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 多態關聯的機器
    public function machines()
    {
        return $this->morphMany(Machine::class, 'storeable');
    }
}
