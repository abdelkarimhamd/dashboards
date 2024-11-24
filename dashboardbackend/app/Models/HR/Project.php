<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'hr_project';

    protected $fillable = [
        'project_name',
        'start_date',
        'end_date',
        'description',
    ];

    // Relationships
    public function employees()
    {
        return $this->hasMany(Employee::class, 'project_id');
    }

    public function orgCharts()
    {
        return $this->hasMany(OrgChart::class, 'project_id');
    }
}
