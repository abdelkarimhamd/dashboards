<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitOutProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_name',
        'project_type',
        'location',
        'project_value',
        'approved_vo',
        'revised_project_value',
        'duration',
        'commencement_date',
        'completion_date',
        'approved_eot',
        'updated_completion_date',
        'project_manager_name',
        'branch',
    ];
}
