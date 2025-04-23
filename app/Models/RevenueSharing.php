<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueSharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'total_revenue',
        'company_share',
        'arcade_share',
        'machine_owner_share',
    ];

    /**
     * 與 Machine 關聯
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
