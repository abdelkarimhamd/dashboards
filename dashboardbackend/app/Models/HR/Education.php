<?php
namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'hr_employee_education';

    protected $fillable = ['employee_id', 'institution_name', 'degree', 'field_of_study', 'start_date', 'end_date', 'description'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
