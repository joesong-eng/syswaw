<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Machine;

class MachineDataRecord extends Model{
    use HasFactory;

    public function machine()
    {
        // 假設 MachineDataRecord 的 chip_id 欄位對應 Machine 的 chip_id 欄位
        // 如果不是，需要調整 foreign_key 和 owner_key
        return $this->belongsTo(Machine::class, 'chip_id', 'chip_id');
    }
}
