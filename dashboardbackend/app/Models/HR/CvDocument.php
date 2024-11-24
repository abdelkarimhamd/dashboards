<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class CvDocument extends Model
{
    protected $table = 'hr_cvdocument';

    protected $fillable = [
        'employee_id',
        'mgi_cv_path',
        'original_cv_path',
        'uploaded_date',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
