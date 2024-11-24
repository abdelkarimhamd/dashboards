<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use SoftDeletes;

    protected $table = 'CRM_deals';

    protected $fillable = [
        'title', 'amount', 'stage', 'close_date', 'reminder_at', 'notification_sent_at',
        'company_id', 'status', 'deal_stage_id', 'user_id', 'created_by', 'updated_by','department',
        // 'deal_id', // Removed as it's likely unnecessary
    ];

    /**
     * Relationship: Deal belongs to Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    // public function tasks()
    // {
    //     return $this->morphMany(Task::class, 'associatedRecord');
    // }


    public function tasks()
    {
        return $this->hasMany(Task::class, 'associated_record_id')->where('associated_record_type', 'deal');
    }
    /**
     * Relationship: Deal belongs to many Contacts
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'crm_deal_contact', 'deal_id', 'contact_id')
                    ->withTimestamps();
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'deal_id');
    }
    /**
     * Relationship: Deal belongs to DealStage
     */
    public function dealStage()
    {
        return $this->belongsTo(DealStage::class);
    }

    /**
     * Relationship: Deal belongs to User (Owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Deal has many Activities
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'deal_id');
    }

    /**
     * Relationship: Deal created by User
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Deal updated by User
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: Deal has many DealLeads
     */
    public function dealLeads()
    {
        return $this->hasMany(DealLead::class);
    }
    // app/Models/CRM/Contact.php
public function dealContacts()
{
    return $this->hasMany(DealContact::class);
}

}
