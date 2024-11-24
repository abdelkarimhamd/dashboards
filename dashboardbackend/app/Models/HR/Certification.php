<?php
namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $table = 'hr_employee_certifications';

    protected $fillable = ['employee_id', 'certification_name', 'institution', 'issue_date', 'expiry_date', 'description'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
