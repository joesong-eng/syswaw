<?php
//app/Models/Machine.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'arcade_id',
        'owner_id',
        'created_by',
        'auth_key_id',
        'machine_id',
        // 將 'machine_type' 替換為 'machine_category'
        'machine_category', //
        'status',
        'is_active',
        'revenue_split',
        'share_pct',
        'coin_input_value',
        'ball_input_value',
        'balls_per_credit',
        'credit_button_value',
        'payout_button_value',
        'payout_type',
        'payout_unit_value',
        'bill_acceptor_enabled',
        'bill_currency',
        'accepted_denominations',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'status' => 'array',
        'revenue_split' => 'float',
        'share_pct' => 'decimal:2',
        'deleted_at' => 'datetime',
        'coin_input_value' => 'decimal:2',
        'ball_input_value' => 'decimal:2',
        'credit_button_value' => 'decimal:2',
        'payout_button_value' => 'decimal:2',
        'payout_unit_value' => 'decimal:2',
        'bill_acceptor_enabled' => 'boolean',
        'accepted_denominations' => 'array',
    ];

    protected $with = ['arcade', 'owner', 'machineAuthKey'];

    // 移除 protected $appends = ['accepted_denominations'];

    // 改用不同的屬性名稱
    public function getParsedAcceptedDenominationsAttribute()
    {
        return $this->attributes['accepted_denominations'] ?
            json_decode($this->attributes['accepted_denominations'], true) : [];
    }

    public function arcade()
    {
        return $this->belongsTo(Arcade::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function machineAuthKey()
    {
        return $this->belongsTo(MachineAuthKey::class, 'auth_key_id');
    }

    public function dataRecords()
    {
        return $this->hasManyThrough(
            MachineDataRecord::class,
            MachineAuthKey::class,
            'id',
            'auth_key_id'
        );
    }

    public function getHasCreditButtonAttribute(): bool
    {
        return $this->credit_button_value !== null;
    }

    public function getHasPayoutButtonAttribute(): bool
    {
        return $this->payout_button_value !== null;
    }

    public function getCalculatedPayoutButtonQuantityAttribute(): ?float
    {
        if ($this->payout_button_value !== null && $this->payout_unit_value !== null && $this->payout_unit_value > 0) {
            return round($this->payout_button_value / $this->payout_unit_value, 2);
        }
        return null;
    }

    public function getActualCreditActionValueAttribute(): ?float
    {
        if ($this->credit_button_value !== null) {
            return (float) $this->credit_button_value;
        }
        return null;
    }
}
