<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $table = 'CRM_contacts';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'job_title', 'company_id','created_by','updated_by'
    ];
// app/Models/CRM/Contact.php
public function tasks()
{
    return $this->hasMany(Task::class, 'associated_record_id')->where('associated_record_type', 'contact');
}
public function deals()
{
    return $this->belongsToMany(Deal::class, 'crm_deal_contact', 'contact_id', 'deal_id')
                ->using(DealContact::class)
                ->withTimestamps();
}

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
