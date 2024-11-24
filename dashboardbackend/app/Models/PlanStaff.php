<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanStaff extends Model
{
    use HasFactory;

    protected $table = 'planstaff';
    protected $primaryKey = 'planstaffID'; 
    public $incrementing = true;
    protected $fillable = [
        'ProjectID', 'M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12', 
        'Total', 'VarianceYTD', 'Performance', 'Year','branch'
    ];

    public function projectDetail()
    {
        return $this->belongsTo(ProjectDetail::class, 'ProjectID', 'ProjectID');
    }
}
