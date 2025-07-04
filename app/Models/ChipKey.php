<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChipKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'expires_at',
        'owner_id',
        'created_by',
        'status',
        'printed',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
