<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $table = 'hr_Interview';

    protected $fillable = [
        'employee_id',
        'interview_stage',
        'scheduled_date',
        'interviewer_by',
        'feedback',
        'rating',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
