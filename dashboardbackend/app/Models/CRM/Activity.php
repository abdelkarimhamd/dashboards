<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

    protected $table = 'CRM_activities';

    protected $fillable = [
        'type',
        'description',
        'outcome',
        'activity_date',
        'company_id',
        'lead_id',
        'email_id',
        'user_id',
        "email_from",
        "email_to",
        "email_subject",
        "email_received_at",
        'deal_id', // Ensure 'deal_id' is fillable
    ];

    /**
     * Relationship: Activity belongs to Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Activity belongs to Lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Relationship: Activity belongs to User
     */
    // In App\Models\CRM\Activity.php

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Activity has many Tasks
     */
    public function tasks()
    {
        return $this->morphMany(Task::class, 'associatedRecord');
    }

    /**
     * Relationship: Activity has many Notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * New Relationship: Activity belongs to Deal
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }
}
