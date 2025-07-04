<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineDataRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'auth_key_id',
        'token',
        'machine_type',
        'credit_in',
        'coin_out',
        'return_value',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function machineAuthKey()
    {
        return $this->belongsTo(MachineAuthKey::class, 'auth_key_id');
    }

    public function machine()
    {
        return $this->hasOneThrough(
            Machine::class,
            MachineAuthKey::class,
            'id',
            'id',
            'auth_key_id',
            'machine_id'
        );
    }

    public function extendedData()
    {
        return $this->hasMany(MachineDataExtended::class, 'record_id');
    }
}
