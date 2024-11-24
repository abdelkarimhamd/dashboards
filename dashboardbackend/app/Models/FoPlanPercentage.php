<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoPlanPercentage extends Model
{
    use HasFactory;
    protected $table = 'fo_plan_percentage';

    // The attributes that are mass assignable
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

    // Define the relationship between the fo_plan_monthly_base table and the fit_out_projects table
    public function project()
    {
        return $this->belongsTo(FitOutProject::class, 'projectId');
    }
}
