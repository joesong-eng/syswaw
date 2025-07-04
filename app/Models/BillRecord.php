<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillRecord extends Model
{
    protected $fillable = ['record_id', 'bill_denomination', 'bill_count', 'timestamp'];
    protected $casts = ['timestamp' => 'datetime'];
}
