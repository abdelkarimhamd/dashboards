<?php

namespace App\Models\ProcurementModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementProjects extends Model
{
    use HasFactory;
    protected $table = 'procurement_projects';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $fillable = [
        "id","project_name","project_type"
    ];  
    public $timestamps = true;	
}