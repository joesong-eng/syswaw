<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MachineDataRecord; // 如果需要反向關聯
use App\Models\Arcade;
use App\Models\User;

class Machine extends Model{
    use HasFactory, SoftDeletes;
    protected $table = 'machines';
    protected $fillable = [
        'name','arcade_id','owner_id','created_by','chip_id','machine_type','status','is_active','revenue_split',
    ];
    protected $casts = [
        'status' => 'array','is_active' => 'boolean',
    ];
    // 與 Arcade 的關聯（多對一）
    public function arcade(){
        return $this->belongsTo(Arcade::class, 'arcade_id');
    }
    // 與 Owner 的關聯
    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }
    // 與 CreatedBy 的關聯
    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
    public function chip(){
        return $this->belongsTo(ChipKey::class, 'chip_id');
    }
    // 如果需要獲取某台機器的所有數據記錄 (可選)
    public function dataRecords(){
        // 假設 Machine 的 chip_id 欄位對應 MachineDataRecord 的 chip_id 欄位
        return $this->hasMany(MachineDataRecord::class, 'chip_id', 'chip_id');
    }

}