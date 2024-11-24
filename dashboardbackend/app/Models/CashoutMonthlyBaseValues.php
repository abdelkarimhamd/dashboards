<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashoutMonthlyBaseValues extends Model
{
    use HasFactory;
    protected $table = 'cashout_monthly_base_values';
    protected $fillable = [
        'projectId',
        'branch',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'december',
        'year'
        
    ];
}
