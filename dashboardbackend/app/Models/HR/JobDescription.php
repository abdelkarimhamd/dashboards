<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobDescription extends Model
{
    protected $table = 'hr_job_description'; // Ensure the table name is plural, matching Laravel's convention or your actual table name

    protected $fillable = [
        'title',
        'document_path',
    ];

    // Relationships
    public function employees()
    {
        // Update the foreign key to match the `hr_job_description_id` in the `hr_employees` table
        return $this->hasMany(Employee::class, 'hr_job_description_id');
    }
}
