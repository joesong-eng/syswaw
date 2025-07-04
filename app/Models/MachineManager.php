<?php

namespace App\Models;
// app/Models/MachineManager.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'user_id',
    ];

    // 定義與 Machine 和 User 的關聯
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
