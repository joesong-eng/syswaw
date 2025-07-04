<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineDataExtended extends Model
{
    use HasFactory;

    protected $table = 'machine_data_extended';

    protected $fillable = [
        'record_id',
        'data_type',
        'value',
    ];

    public function record()
    {
        return $this->belongsTo(MachineDataRecord::class, 'record_id');
    }
}
