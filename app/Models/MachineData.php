<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineData extends Model
{
    use HasFactory;

    protected $table = 'machine_data';

    protected $fillable = [
        'machine_id',
        'arcade_id',
        'auth_key_id',
        'machine_type',
        'credit_in',
        'ball_in',
        'ball_out',
        'coin_out',
        'assign_credit',
        'settled_credit',
        'bill_denomination',
        'error_code',
        'timestamp',
    ];

    protected $casts = [
        'credit_in' => 'integer',
        'ball_in' => 'integer',
        'ball_out' => 'integer',
        'coin_out' => 'integer',
        'assign_credit' => 'integer',
        'settled_credit' => 'integer',
        'bill_denomination' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function arcade()
    {
        return $this->belongsTo(Arcade::class);
    }

    public function authKey()
    {
        return $this->belongsTo(MachineAuthKey::class, 'auth_key_id');
    }
}
