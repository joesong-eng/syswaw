<?php
// app/Models/MachineAuthKey.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 引入 SoftDeletes trait

class MachineAuthKey extends Model
{
    use HasFactory, SoftDeletes; // 使用 HasFactory 和 SoftDeletes

    /**
     * 與模型關聯的資料表。
     *
     * @var string
     */
    protected $table = 'machine_auth_keys';

    /**
     * 可批量賦值的屬性。
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'auth_key',
        'chip_hardware_id',
        'expires_at',
        'machine_id',
        'owner_id',
        'created_by',
        'status',
        'printed',
    ];

    /**
     * 應被轉換為原生類型的屬性。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'printed' => 'boolean',
    ];

    /**
     * 獲取與此金鑰關聯的機器。
     */
    public function machine()
    {
        // 根據 machine_id 關聯到 Machine 模型
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    /**
     * 獲取此金鑰的擁有者。
     */
    public function owner()
    {
        // 根據 owner_id 關聯到 User 模型
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * 獲取創建此金鑰記錄的用戶。
     */
    public function creator()
    {
        // 根據 created_by 關聯到 User 模型
        return $this->belongsTo(User::class, 'created_by');
    }
}
