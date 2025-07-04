<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArcadeKey extends Model
{
    use HasFactory;

    protected $table = 'arcade_keys';

    protected $fillable = [
        'token',
        'expires_at',
        'used',
        'authenticatable_id',
        'authenticatable_type',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function authenticatable()
    {
        return $this->morphTo();
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function arcade()
    {
        return $this->hasOne(Arcade::class, 'authorize_key', 'id');
    }
}
