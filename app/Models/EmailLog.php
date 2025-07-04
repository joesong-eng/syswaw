<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = ['user_id', 'email', 'type', 'success', 'error_message'];
}
