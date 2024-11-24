<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    protected $table = 'project_details';
    protected $primaryKey = 'ProjectID';
    public $incrementing = true;
    protected $fillable = ['ProjectName', 'YearSelected', 'MainScope','ProjectManager','branch'];
    public function manager() {
        return $this->belongsTo(Pluser::class, 'ProjectManager', 'username');
    }

    use HasFactory;
}
