<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    protected $table = 'hr_employee_skills';

    protected $fillable = ['employee_id', 'skill_name', 'proficiency_level'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
