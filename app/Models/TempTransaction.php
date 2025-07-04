<?php
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
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereBallIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereBallOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereCreditIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereMachineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TempTransaction extends Model
{
    use HasFactory;

    protected $table = 'temp_transactions';

    protected $fillable = [
        'token','machine_id','credit_in', 
        'ball_in', 'ball_out', 'transaction_id'
    ];
}
