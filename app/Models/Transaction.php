<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'arcade_id',
        'machine_id',
        'type',
        'amount',
        'balance',
    ];

    /**
     * 與 User 關聯
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 與 Store 關聯
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * 與 Machine 關聯
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
