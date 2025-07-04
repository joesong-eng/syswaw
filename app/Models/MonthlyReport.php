<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    use HasFactory;

    protected $guarded = []; // 允許所有欄位被批量賦值
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'date',
    ];
    public function details()
    {
        return $this->hasMany(MonthlyReportDetail::class);
    }
}
