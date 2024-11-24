<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CRM\User;
use App\Models\CRM\Deal;
use App\Models\CRM\Contact;
use App\Models\CRM\Company;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use SoftDeletes;

    protected $table = 'crm_tasks_manager';

    protected $fillable = [
        'task_title',
        'task_type',
        'associated_record_type',
        'associated_record_id',
        'priority',
        'created_by',
        'updated_by',
        'assigned_to',
        'due_date',
        'reminder_at',
        'description',
        'status',
    ];

    protected $dates = [
        'due_date',
        'reminder_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the user who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the task.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user to whom the task is assigned.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the associated record (deal, contact, or company) for the task.
     */
    public function associatedRecord()
    {
        return $this->morphTo(null, 'associated_record_type', 'associated_record_id');
    }
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the tasks created by the user.
     */
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get the tasks updated by the user.
     */
    public function updatedTasks()
    {
        return $this->hasMany(Task::class, 'updated_by');
    }
// app/Models/CRM/Task.php

public function assignedUser()
{
    return $this->belongsTo(User::class, 'assigned_to');
}
public function scopeAggregateByPriority($query)
    {
        return $query->select(
                DB::raw('TRIM(LOWER(priority)) as normalized_priority'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('TRIM(LOWER(priority))'));
    }
    /**
     * Scope a query to only include tasks of a given status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
}
