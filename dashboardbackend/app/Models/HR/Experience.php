<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $table = 'hr_employee_experience';

    protected $fillable = ['employee_id', 'company_name', 'position', 'start_date', 'end_date', 'description'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
