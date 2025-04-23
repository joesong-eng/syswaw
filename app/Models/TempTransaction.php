<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempTransaction extends Model
{
    use HasFactory;

    protected $table = 'temp_transactions';

    protected $fillable = [
        'token','machine_id','credit_in', 
        'ball_in', 'ball_out', 'transaction_id'
    ];
}
