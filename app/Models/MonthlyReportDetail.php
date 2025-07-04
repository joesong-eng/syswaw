<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyReportDetail extends Model
{
    use HasFactory;

    protected $guarded = []; // 允許所有欄位被批量賦值

    public function report()
    {
        return $this->belongsTo(MonthlyReport::class, 'monthly_report_id');
    }

    public function reportable()
    {
        return $this->morphTo();
    }
}
