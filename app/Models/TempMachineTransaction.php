<?php
// App/Models/TempMachineTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempMachineTransaction extends Model{
    use HasFactory;
    protected $table = 'temp_transactions';
    protected $fillable = [
        'transaction_id', 'machine_id', 'token', 
        'credit_in', 'ball_in', 'ball_out', 'source',
    ];
}