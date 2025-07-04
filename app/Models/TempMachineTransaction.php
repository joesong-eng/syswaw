<?php
// App/Models/TempMachineTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $transaction_id
 * @property string $machine_id
 * @property string $token
 * @property string $credit_in
 * @property string $ball_in
 * @property string $ball_out
 * @property string|null $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereBallIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereBallOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereCreditIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereMachineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempMachineTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TempMachineTransaction extends Model{
    use HasFactory;
    protected $table = 'temp_transactions';
    protected $fillable = [
        'transaction_id', 'machine_id', 'token', 
        'credit_in', 'ball_in', 'ball_out', 'source',
    ];
}