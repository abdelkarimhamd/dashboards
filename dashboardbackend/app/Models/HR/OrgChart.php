<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class OrgChart extends Model
{
    protected $table = 'hr_orgchart';

    protected $fillable = [
        'employee_id',
        'project_id',
        'position_id',
        'manager_id',
        'hierarchy_level',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
