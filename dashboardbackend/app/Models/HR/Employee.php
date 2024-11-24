<?php


namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'hr_employee';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'position_id',
        'project_id',
        'status',
        'reported_by',
        'expectation_start_date',
        'actual_start_date',
        'expectation_salary',
        'current_salary',
        'job_document_id',
        'hr_job_description_id',
        'years_experience',      // Added field
        'proposed_position',     // Added field
        'nationality',           // Added field
        'languages',             // Added field
        'about_me',   
        'image_path', 
        'job_title',          // Added field
    ];

    // Relationships
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
// Employee.php
public function jobDescription()
{
    return $this->belongsTo(JobDescription::class, 'hr_job_description_id');
}

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function orgChart()
    {
        return $this->hasOne(OrgChart::class, 'employee_id');
    }

    public function jobDocument()
    {
        return $this->belongsTo(JobDescription::class, 'job_document_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function cvDocuments()
    {
        return $this->hasMany(CvDocument::class, 'employee_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'employee_id');
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class, 'employee_id');
    }

    public function education()
    {
        return $this->hasMany(Education::class, 'employee_id');
    }

    public function skills()
    {
        return $this->hasMany(Skills::class, 'employee_id');
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class, 'employee_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusHistory::class, 'employee_id');
    }
}
