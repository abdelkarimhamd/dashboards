<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpectedSalaryValue extends Model
{
    use HasFactory;
    protected $table = 'expected_salary_value';

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
        'year',
    ];
    public function projectDetail()
    {
        return $this->belongsTo(ProjectDetail::class, 'ProjectID', 'ProjectID');
    }

}
